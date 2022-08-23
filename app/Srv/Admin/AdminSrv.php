<?php


namespace App\Srv\Admin;


use App\Models\Admin;
use App\Srv\Srv;

class AdminSrv extends Srv
{
    public function login($p)
    {
        if (!$admin = Admin::where('username', $p['username'])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '用户名错误');
        }
        if ($admin->password != $p['password']) {
            return $this->returnData(ERR_PARAM_ERR, '密码错误');
        }
        $admin->token = md5($p['username'] . time());
        $admin->save();
        return $this->returnData(ERR_SUCCESS, '', $admin->token);
    }

    public function info()
    {
        return $this->returnData(ERR_SUCCESS, '', $this->getAdmin());
    }
}
