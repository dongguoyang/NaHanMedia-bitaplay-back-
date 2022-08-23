<?php


namespace App\Srv\Admin;

use App\Models\UserDownloadRecord;
use App\Models\UserInviteReward;
use App\Models\UserWallet;
use App\Models\UserWalletRecord;
use App\Models\UserWithdraw;
use App\Srv\MyException;
use App\Srv\Srv;
use Illuminate\Support\Facades\DB;

class UserFinanceSrv extends Srv
{

    public function withdraw($p)
    {
        $query = UserWithdraw::with('user');
        if ($p['id'] > 0) {
            $query->where('user_id', $p['id']);
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
            $withdraw = UserWithdraw::where('id', $p['id'])->lockForUpdate()->first();
            if (!$withdraw || $withdraw->status != WITHDRAW_STATUS_PENDING) {
                throw new MyException('已处理');
            }
            $wallet = UserWallet::where('user_id', $withdraw->user_id)->lockForUpdate()->first();
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

            // 修改用户钱包记录
            UserWalletRecord::where([
                'user_id' => $withdraw->user_id,
                'order_id' => $withdraw->id,
                'type' => USER_WALLET_RECORD_WITHDRAW,
            ])->update([
                'status' => $p['status'] == WITHDRAW_STATUS_ABLE ? 3 : 2
            ]);

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

    public function downloadReward($p)
    {
        $query = UserDownloadRecord::with('app', 'user')
            ->where('user_reward_amount', '>', 0);
        if ($p['id'] > 0) {
            $query->where('user_id', $p['id']);
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

    public function recommendReward($p)
    {
        $query = UserInviteReward::with('provider', 'user');
        if ($p['id'] > 0) {
            $query->where('user_id', $p['id']);
        }
        if ($p['number']) {
            $query->where('number', 'like', "%{$p['number']}%");
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }
}
