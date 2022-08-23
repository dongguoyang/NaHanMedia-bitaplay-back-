<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\AppSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppController extends Controller
{
    public function list(Request $request, AppSrv $srv)
    {
        $p = $request->only('provider_id', 'name', 'status', 'app_id', 'is_recommend');
        $validator = Validator::make($p, [
            'provider_id' => 'present',
            'name' => 'present',
            'app_id' => 'present',
            'status' => 'present',
            'is_recommend' => 'present',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->list($p));
    }

    public function version(Request $request, AppSrv $srv)
    {
        $p = $request->only('name', 'status', 'app_id');
        $validator = Validator::make($p, [
            'name' => 'present',
            'app_id' => 'present',
            'status' => 'present',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->version($p));
    }


    public function editVersionStatus(Request $request, AppSrv $srv)
    {
        $p = $request->only('id', 'status', 'refuse_reason');
        $validator = Validator::make($p, [
            'id' => 'required|min:1',
            'refuse_reason' => 'required_if:status,3',
            'status' => 'required|in:2,3',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->editVersionStatus($p));
    }

    public function editRecommend(Request $request, AppSrv $srv)
    {
        $p = $request->only('id');
        $validator = Validator::make($p, [
            'id' => 'required|min:1',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->editRecommend($p['id']));
    }

    public function editDownloadCount(Request $request, AppSrv $srv)
    {
        $p = $request->only('id', 'count');
        $validator = Validator::make($p, [
            'id' => 'required|min:1',
            'count' => 'required|min:0'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->editDownloadCount($p['id'], $p['count']));
    }


    public function downloadRecord(Request $request, AppSrv $srv)
    {
        $p = $request->only('user_id', 'name', 'category', 'time');
        $validator = Validator::make($p, [
            'user_id' => 'present',
            'name' => 'present',
            'category' => 'present',
            'time' => 'present',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->downloadRecord($p));
    }
}
