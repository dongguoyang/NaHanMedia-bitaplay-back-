<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Srv\Admin\UsersAddressBookSrv;

class UsersAddressBookController extends Controller{

    public function list(Request $request,UsersAddressBookSrv $srv){
        $p = $request->only('phone' );
        $validator = Validator::make($p, [
            'phone' => 'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->realNameList($p));
    }
}