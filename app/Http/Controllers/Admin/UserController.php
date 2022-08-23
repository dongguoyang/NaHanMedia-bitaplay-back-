<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\UserSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function list(Request $request, UserSrv $srv)
    {
        $p = $request->only('status', 'tel', 'id','is_cert');
        $validator = Validator::make($p, [
            'status' => 'present',
            'tel' => 'present',
            'id' => 'present',
            'is_cert'=>'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->list($p));
    }


    public function editStatus(Request $request, UserSrv $srv)
    {
        return $this->responseDirect($srv->editStatus($request->input('id', 0)));
    }
}
