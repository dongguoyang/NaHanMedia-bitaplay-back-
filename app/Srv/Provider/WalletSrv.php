<?php


namespace App\Srv\Provider;

use App\Models\ProviderRechargeBalanceRecord;
use App\Models\ProviderWallet;
use App\Models\ProviderWithdraw;
use App\Models\ProviderWithdrawAccount;
use App\Models\System;
use App\Srv\MyException;
use App\Srv\Srv;
use App\Srv\Utils\AlipaySrv;
use Illuminate\Support\Facades\DB;

class WalletSrv extends Srv
{
    public function index()
    {
        $provider = $this->getProvider();
        $wallet = $provider->wallet->toArray();
        $wallet['set_pwd'] = $provider->trans_password ? 1 : 0;
        return $this->returnData(ERR_SUCCESS, '', $wallet);
    }

    public function recharge($p)
    {
        $provider = $this->getProvider();
        $localNum = 'R' . time() . rand(1000000, 9999999);
        if ($p['amount'] < 8000) {
            return $this->returnData(ERR_PARAM_ERR, '最少充值8000元');
        }
        ProviderRechargeBalanceRecord::create([
            'provider_id' => $provider->id,
            'number' => $localNum,
            'amount' => $p['amount'] * 100,
            'status' => PAYMENT_STATUS_PENDING,
            'payment_method' => $p['payment_method'],
        ]);
        $payRes = (new PaySrv($p['payment_method']))->pay($p['amount'] * 100, $localNum, '余额充值');
        return $payRes;
    }

    public function queryRecharge($number)
    {
        try {
            DB::beginTransaction();
            if (!$record = ProviderRechargeBalanceRecord::where('number', $number)->lockForUpdate()->first()) {
                throw new MyException('非法操作');
            }
            if ($record->status == PAYMENT_STATUS_ABLE) {
                DB::commit();
                return $this->returnData(ERR_SUCCESS, '', ['status' => PAYMENT_STATUS_ABLE, 'payment_number' => '']);
            }
            $res = (new PaySrv($record->payment_method))->check($number);
            if ($res['code'] != ERR_SUCCESS) {
                throw new MyException('查询失败');
            }
            if ($res['data']['status'] == PAYMENT_STATUS_ABLE) {
                $record->payment_number = $res['data']['payment_number'];
                $record->status = PAYMENT_STATUS_ABLE;
                $record->save();

                $wallet = ProviderWallet::where('provider_id', $record->provider_id)->lockForUpdate()->first();
                $wallet->balance += $record->amount;
                $wallet->save();
            }
            DB::commit();
            return $res;
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '服务器错误');
        }
    }

    public function saveWithdrawAccount($p)
    {
        $provider = $this->getProvider();
        if ($provider->trans_password == '') {
            return $this->returnData(ERR_FAILED, '请设置支付密码');
        }
        if ($provider->trans_password != $p['trans_password']) {
            return $this->returnData(ERR_FAILED, '支付密码错误');
        }
        if (!$account = ProviderWithdrawAccount::where('provider_id', $provider->id)->first()) {
            $account = new ProviderWithdrawAccount();
            $account->provider_id = $provider->id;
        }
        if ($p['type'] == WITHDRAW_ACCOUNT_BANK) {
            $account->bank_name = $p['bank_name'];
            $account->bank_account_name = $p['bank_account_name'];
            $account->bank_number = $p['bank_number'];
        } else {
            $account->alipay_account_name = $p['alipay_account_name'];
            $account->alipay_number = $p['alipay_number'];
        }
        $account->save();
        return $this->returnData();
    }

    public function withdrawAccount()
    {
        $account = $this->getProvider()->withdrawAccount;
        return $this->returnData(ERR_SUCCESS, '', $account);
    }

    public function withdraw($p)
    {
        $provider = $this->getProvider();
        if ($provider->trans_password == '' || $provider->trans_password != $p['trans_password']) {
            return $this->returnData(ERR_FAILED, '支付密码错误');
        }
        $account = $provider->withdrawAccount;
        if (!$account) {
            return $this->returnData(ERR_FAILED, '请设置提现账户');
        }
        if (($p['type'] == WITHDRAW_ACCOUNT_BANK && $account->bank_number == '') || ($p['type'] == WITHDRAW_ACCOUNT_ALIPAY && $account->alipay_number == '')) {
            return $this->returnData(ERR_FAILED, '请设置提现账户');
        }
        if ($p['amount'] <= 0) {
            return $this->returnData(ERR_PARAM_ERR, '提现金额必须大于￥0');
        }
        $amount = $p['amount'] * 100;
        $fee = System::where('key', 'fee')->first();
        try {
            DB::beginTransaction();
            $wallet = ProviderWallet::where('provider_id', $provider->id)->lockForUpdate()->first();
            if ($wallet->balance < $amount) {
                throw new MyException('余额不足');
            }
            // 更新余额
            $wallet->balance -= $amount;
            $wallet->frozen_balance += $amount;
            $wallet->save();

            $number = "PW" . time() . rand(100000, 999999);
            $fee = ceil($amount * $fee['value']['withdraw_fee']);

            // 写提现申请
            $data['provider_id'] = $provider->id;
            $data['number'] = $number;
            $data['fee'] = $fee;
            $data['amount'] = $amount;
            $data['status'] = WITHDRAW_STATUS_PENDING;
            $data['payment_method'] = $p['type'];
            if ($p['type'] == WITHDRAW_ACCOUNT_ALIPAY) {
                $data['name'] = $account->alipay_account_name;
                $data['alipay_number'] = $account->alipay_number;
            } else {
                $data['name'] = $account->bank_account_name;
                $data['bank_number'] = $account->bank_number;
                $data['bank_name'] = $account->bank_name;
            }
            ProviderWithdraw::create($data);

            // 提交提现申请
            $res = (new AlipaySrv())->transToAlipayAccount($amount - $fee, $number, $account->alipay_number,$account->alipay_account_name, '提现');
            if ($res['code'] != 0) {
                throw new MyException('请检查支付宝账户信息是否正确');
            }

            DB::commit();
            return $this->returnData();
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '服务器错误');
        }
    }

    public function withdrawRecord($p)
    {
        $query = ProviderWithdraw::orderBy('id', 'desc');
        if ($p['status'] > 0) {
            $query->where('status', $p['status']);
        }
        if ($p['number']) {
            $query->where('number', 'like', "%{$p['number']}%");
        }
        $list = $query->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function rechargeRecord($p)
    {
        $query = ProviderRechargeBalanceRecord::orderBy('id', 'desc');
        if ($p['status'] > 0) {
            $query->where('status', $p['status']);
        }
        if ($p['number']) {
            $query->where('number', 'like', "%{$p['number']}%");
        }
        $list = $query->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }
}

