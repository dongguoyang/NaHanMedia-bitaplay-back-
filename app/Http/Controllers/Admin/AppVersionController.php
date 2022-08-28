<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\AppVersionSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppVersionController extends Controller{

    public function list(Request $request, AppVersionSrv $srv){
        $p = $request->only('status', 'version' );
        $validator = Validator::make($p, [
            'status' => 'present',
            'version' => 'present',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->list($p));
    }

    public function save(Request $request, AppVersionSrv $srv){
        $p = $request->only('status', 'version','url','remark');
        $validator = Validator::make($p, [
            'status' => 'present',
            'version' => 'present',
            'url' => 'present',
            'remark' => 'present',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->save($p));
    }

    public function editStatus(Request $request, AppVersionSrv $srv){
        return $this->responseDirect($srv->editStatus($request->input('id', 0)));
    }
}
