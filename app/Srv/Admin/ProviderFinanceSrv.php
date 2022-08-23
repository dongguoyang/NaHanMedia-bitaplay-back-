<?php


namespace App\Srv\Admin;

use App\Models\ProviderApp;
use App\Models\ProviderAppChainRecord;
use App\Models\ProviderAppRechargeFuelRecord;
use App\Models\ProviderAppRechargeRewardRecord;
use App\Models\ProviderAppRechargeThirdLoginRecord;
use App\Models\ProviderRechargeBalanceRecord;
use App\Models\ProviderWallet;
use App\Models\ProviderWithdraw;
use App\Models\User;
use App\Models\UserDownloadRecord;
use App\Models\UserThirdLoginRecord;
use App\Srv\MyException;
use App\Srv\Srv;
use Illuminate\Support\Facades\DB;

class ProviderFinanceSrv extends Srv
{
    public function rechargeBalance($p)
    {
        $query = ProviderRechargeBalanceRecord::with('provider')
            ->where('status', PAYMENT_STATUS_ABLE);
        if ($p['id'] > 0) {
            $query->where('provider_id', $p['id']);
        }
        if ($p['number']) {
            $query->where('number', 'like', "%{$p['number']}%");
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function withdraw($p)
    {
        $query = ProviderWithdraw::with('provider');
        if ($p['id'] > 0) {
            $query->where('provider_id', $p['id']);
        }
        if ($p['number']) {
            $query->where('number', 'like', "%{$p['number']}%");
        }
        if ($p['status'] > 0) {
            $query->where('status', $p['status']);
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function doWithdraw($p)
    {
        try {
            DB::beginTransaction();
            $withdraw = ProviderWithdraw::where('id', $p['id'])->lockForUpdate()->first();
            if (!$withdraw || $withdraw->status != WITHDRAW_STATUS_PENDING) {
                throw new MyException('已处理');
            }
            $wallet = ProviderWallet::where('provider_id', $withdraw->provider_id)->lockForUpdate()->first();
            if ($wallet->frozen_balance < $withdraw->amount) {
                throw new MyException('请注意，账户有误');
            }
            $wallet->frozen_balance -= $withdraw->amount;
            if ($p['status'] == WITHDRAW_STATUS_ABLE) {
                $withdraw->evidence = $p['evidence'];
            } else {
                $wallet->balance += $withdraw->amount;
                $withdraw->refuse_reason = $p['refuse_reason'];
            }
            $withdraw->status = $p['status'];
            $withdraw->save();
            $wallet->save();
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

    public function rechargeLogin($p)
    {
        $query = ProviderAppRechargeThirdLoginRecord::with('provider', 'app');
        if ($p['id'] > 0) {
            $query->where('provider_id', $p['id']);
        }
        if ($p['number']) {
            $query->where('number', 'like', "%{$p['number']}%");
        }
        if ($p['app_id']) {
            $query->whereHas('app', function ($query) use ($p) {
                $query->where('app_id', 'like', "%{$p['app_id']}%");
            });
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }


    public function rechargeFuel($p)
    {
        $query = ProviderAppRechargeFuelRecord::with('provider', 'app');
        if ($p['id'] > 0) {
            $query->where('provider_id', $p['id']);
        }
        if ($p['number']) {
            $query->where('number', 'like', "%{$p['number']}%");
        }
        if ($p['app_id']) {
            $query->whereHas('app', function ($query) use ($p) {
                $query->where('app_id', 'like', "%{$p['app_id']}%");
            });
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function rechargeReward($p)
    {
        $query = ProviderAppRechargeRewardRecord::with('provider', 'app');
        if ($p['id'] > 0) {
            $query->where('provider_id', $p['id']);
        }
        if ($p['number']) {
            $query->where('number', 'like', "%{$p['number']}%");
        }
        if ($p['app_id']) {
            $query->whereHas('app', function ($query) use ($p) {
                $query->where('app_id', 'like', "%{$p['app_id']}%");
            });
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }
}
