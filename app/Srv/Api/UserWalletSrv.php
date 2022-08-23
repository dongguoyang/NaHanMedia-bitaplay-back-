<?php


namespace App\Srv\Api;


use App\Models\System;
use App\Models\UserRecharge;
use App\Models\UserWallet;
use App\Models\UserWalletRecord;
use App\Models\UserWithdraw;
use App\Models\UserWithdrawAccount;
use App\Srv\MyException;
use App\Srv\Srv;
use App\Srv\Utils\AlipaySrv;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserWalletSrv extends Srv
{
    public function info()
    {
        $wallet = $this->getUser()->wallet;
        return $this->returnData(ERR_SUCCESS, '', $wallet);
    }

    public function saveWithdrawAccount($p)
    {
        $user = $this->getUser();
        if (!$user->trans_password) {
            return $this->returnData(ERR_USER_NOT_TRANS_PASSWORD, '请设置支付密码');
        }
        if ($user->trans_password != $p['trans_password']) {
            return $this->returnData(ERR_PARAM_ERR, '密码错误');
        }

        $account = $user->withdrawAccount;
        if (!$account) {
            $account = new UserWithdrawAccount();
            $account->user_id = $user->id;
        }

        if ($p['type'] == WITHDRAW_ACCOUNT_ALIPAY) {
            $account->alipay_account_name = $p['alipay_account_name'];
            $account->alipay_number = $p['alipay_number'];
        } elseif ($p['type'] == WITHDRAW_ACCOUNT_BANK) {
            $account->bank_name = $p['bank_name'];
            $account->bank_account_name = $p['bank_account_name'];
            $account->bank_number = $p['bank_number'];
        }
        $account->save();
        return $this->returnData();
    }

    public function withdrawAccount()
    {
        $account = $this->getUser()->withdrawAccount;
        return $this->returnData(ERR_SUCCESS, '', $account);
    }

    public function withdraw($p)
    {
        $user = $this->getUser();
        if (!$user->trans_password) {
            return $this->returnData(ERR_USER_NOT_TRANS_PASSWORD, '请设置支付密码');
        }
        if ($user->trans_password != $p['trans_password']) {
            return $this->returnData(ERR_USER_NOT_TRANS_PASSWORD, '密码错误');
        }
        if ($p['amount'] <= 0) {
            return $this->returnData(ERR_USER_NOT_TRANS_PASSWORD, '提现金额不得小于0.01元');
        }

        $withdrawAccount = $user->withdrawAccount;
        if (!$withdrawAccount) {
            return $this->returnData(ERR_USER_NOT_WITHDRAW_ACCOUNT, '请添加提现账户');
        }

        if (($p['type'] == WITHDRAW_ACCOUNT_ALIPAY && !$withdrawAccount->alipay_number) || ($p['type'] == WITHDRAW_ACCOUNT_BANK && !$withdrawAccount->bank_number)) {
            return $this->returnData(ERR_USER_NOT_WITHDRAW_ACCOUNT, '请添加对应的提现账户');
        }

        $fee = System::where('key', 'fee')->first();


        try {
            DB::beginTransaction();
            $wallet = UserWallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->first();
            if ($wallet->balance < $p['amount']) {
                throw new MyException('余额不足');
            }
            $wallet->balance -= $p['amount'];
            $wallet->frozen_balance += $p['amount'];
            $wallet->save();

            $number = 'UW' . time() . rand(100000, 999999);
            $fee = ceil($p['amount'] * $fee['value']['withdraw_fee']);

            $insertData = [
                'user_id' => $user->id,
                'amount' => $p['amount'],
                'payment_method' => $p['type'],
                'status' => WITHDRAW_STATUS_PENDING,
                'number' => $number,
                'fee' => $fee,
            ];
            if ($p['type'] == WITHDRAW_ACCOUNT_ALIPAY) {
                $insertData['name'] = $withdrawAccount->alipay_account_name;
                $insertData['alipay_number'] = $withdrawAccount->alipay_number;
            } elseif ($p['type'] == WITHDRAW_ACCOUNT_BANK) {
                $insertData['name'] = $withdrawAccount->bank_account_name;
                $insertData['bank_number'] = $withdrawAccount->bank_number;
                $insertData['bank_name'] = $withdrawAccount->bank_name;
            }
            $withdraw = UserWithdraw::create($insertData);
            //写记录
            UserWalletRecord::create([
                'user_id' => $user->id,
                'order_id' => $withdraw->id,
                'number' => $insertData['number'],
                'payment_method' => PAYMENT_METHOD_BALANCE,
                'amount' => $p['amount'],
                'type' => USER_WALLET_RECORD_WITHDRAW,
                'status' => 1,
            ]);

            // 提交提现申请
            $res = (new AlipaySrv())->transToAlipayAccount($p['amount'] - $fee, $number, $withdrawAccount->alipay_number, $withdrawAccount->alipay_account_name, '提现');
            if ($res['code'] != 0) {
                throw new MyException('请检查支付宝账户信息是否正确');
            }

            DB::commit();
            return $this->returnData(ERR_SUCCESS, '', $number);
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '服务器错误');
        }
    }

    public function records($p)
    {
        $user = $this->getUser();
        $query = UserWalletRecord::where('user_id', $user->id);
        if ($p['type'] > 0) {
            $query->where('type', $p['type']);
        }
        $data = $query->orderBy('id', 'desc')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($data));
    }

    public function queryWithdraw()
    {

    }
}

