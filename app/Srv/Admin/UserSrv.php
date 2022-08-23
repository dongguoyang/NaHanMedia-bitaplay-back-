<?php


namespace App\Srv\Admin;

use App\Models\User;
use App\Srv\Srv;

class UserSrv extends Srv
{
    public function list($p)
    {
        $query = User::with('info', 'wallet', 'info.occupation', 'info.industry');
        if ($p['status'] > 0) {
            $query->where('status', $p['status']);
        }
        if ($p['id'] > 0) {
            $query->where('id', $p['id']);
        }
        if ($p['tel']) {
            $query->where('tel', 'like', "%{$p['tel']}%");
        }
        if ($p['is_cert'] > 0) {
            if ($p['is_cert'] == 1) {
                $query->whereDoesntHave('info', function ($query) {
                    $query->where('id_number', '');
                });
            } else {
                $query->whereHas('info', function ($query) {
                    $query->where('id_number', '!=', '');
                });
            }
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function editStatus($id)
    {
        if (!$user = User::where('id', $id)->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        if ($user->status == USER_STATUS_DEL) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        $user->status = $user->status == USER_STATUS_ABLE ? USER_STATUS_DISABLE : USER_STATUS_ABLE;
        $user->save();
        return $this->returnData();
    }
}
