<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Srv\Admin\UsersAddressBookItemSrv;
use App\Srv\Admin\UsersAddressBookLogSrv;
use App\Srv\Api\UsersAddressBookSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsersAddressBookController extends Controller{

    /**
     * 导入通讯录
     * @param Request $request
     * @param UsersAddressBookSrv $srv
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAddressBook(Request $request,UsersAddressBookSrv $srv)
    {
        $p = $request->all();
        return $this->responseDirect($srv->uploadAddressBook($p));
    }

    /**
     * 获取来电提醒
     * @param Request $request
     * @param UsersAddressBookItemSrv $srv
     * @return \Illuminate\Http\JsonResponse
     */
    public function  incomingPhone(Request $request,UsersAddressBookItemSrv $srv){
        $p = $request->only('phone');
        $validator = Validator::make($p, [
            'phone' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->incomingPhone($p));
    }


    /**
     * 上传来电记录
     * @param Request $request
     * @param UsersAddressBookItemSrv $srv
     */
    public function saveIncomingLog(Request $request,UsersAddressBookItemSrv $srv){
        $p = $request->only('user_book_id','real_name','phone','company','incoming_time','incoming_user_id');
        $validator = Validator::make($p, [
            'user_book_id' => 'required',
            'real_name' => 'required',
            'phone' => 'required',
            'company' => 'required',
            'incoming_time' => 'required',
            'incoming_user_id'=>'required'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->saveIncomingLog($p));
    }

    /**
     * 获取来电记录
     * @param Request $request
     * @param UsersAddressBookItemSrv $srv
     */
    public function incomingLog(Request $request,UsersAddressBookItemSrv $srv){
        $p['page'] = $request->input('page',1);
        return $this->responseDirect($srv->incomingLogList($p));
    }

    /**
     * 修改来电记录
     * @param Request $request
     * @param UsersAddressBookLogSrv $srv
     */
    public function updateIncomingLog(Request $request,UsersAddressBookSrv $srv){
        $p = $request->only('phone','real_name','company');
        $validator = Validator::make($p, [
            'phone' => 'required',
            'real_name' => 'required',
            'company' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->changeRealName($p));
    }

}