<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Srv\Admin\UsersAddressBookLogSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsersAddressBookLogController extends Controller{

    public function list(Request $request,UsersAddressBookLogSrv $srv){
        $p = $request->only('user_book_id' );
        $validator = Validator::make($p, [
            'user_book_id' => 'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->list($p));
    }

}