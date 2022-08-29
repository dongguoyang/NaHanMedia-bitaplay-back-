<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Srv\Admin\UsersAddressBookItemSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsersAddressBookItemController extends Controller{
    public function list(Request $request,UsersAddressBookItemSrv $srv){
        $p = $request->only('phone' );
        $validator = Validator::make($p, [
            'phone' => 'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->list($p));
    }
}
