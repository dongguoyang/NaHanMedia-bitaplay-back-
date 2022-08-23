<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Srv\Admin\AdminSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function login(Request $request, AdminSrv $srv)
    {
        $p = $request->only('username', 'password');
        $validator = Validator::make($p, [
            'username' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->login($p));
    }

    public function info(AdminSrv $srv)
    {
        return $this->responseDirect($srv->info());
    }


}
