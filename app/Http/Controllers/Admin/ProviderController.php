<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\ProviderSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{
    public function list(Request $request, ProviderSrv $srv)
    {
        $p = $request->only('id', 'tel', 'status', 'info_status');
        $validator = Validator::make($p, [
            'id' => 'present',
            'tel' => 'present',
            'status' => 'present',
            'info_status' => 'present',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->list($p));
    }

    public function editStatus(Request $request, ProviderSrv $srv)
    {
        return $this->responseDirect($srv->editStatus($request->input('id', 0)));
    }

    public function info(Request $request, ProviderSrv $srv)
    {
        $p = $request->only('provider_id', 'tel', 'status');
        $validator = Validator::make($p, [
            'provider_id' => 'present',
            'tel' => 'present',
            'status' => 'present',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->info($p));
    }

    public function editInfoStatus(Request $request, ProviderSrv $srv)
    {
        $p = $request->only('id', 'refuse_reason', 'status');
        $validator = Validator::make($p, [
            'id' => 'required',
            'refuse_reason' => 'required_if:status,3',
            'status' => 'required|in:2,3',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->editInfoStatus($p));
    }
}
