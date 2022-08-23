<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\ProviderFinanceSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderFinanceController extends Controller
{
    public function rechargeBalance(Request $request, ProviderFinanceSrv $srv)
    {
        $p = $request->only('id', 'number');
        $validator = Validator::make($p, [
            'id' => 'present',
            'number' => 'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->rechargeBalance($p));
    }

    public function withdraw(Request $request, ProviderFinanceSrv $srv)
    {
        $p = $request->only('id', 'number', 'status');
        $validator = Validator::make($p, [
            'id' => 'present',
            'number' => 'present',
            'status' => 'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->withdraw($p));
    }


    public function doWithdraw(Request $request, ProviderFinanceSrv $srv)
    {
        $p = $request->only('id', 'status', 'evidence', 'refuse_reason');
        $validator = Validator::make($p, [
            'id' => 'required',
            'status' => 'required|in:3,4',
            'refuse_reason' => 'required_if:status,4',
            'evidence' => 'required_if:status,3',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->doWithdraw($p));
    }

    public function rechargeLogin(Request $request, ProviderFinanceSrv $srv)
    {
        $p = $request->only('id', 'number', 'app_id');
        $validator = Validator::make($p, [
            'id' => 'present',
            'number' => 'present',
            'app_id' => 'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->rechargeLogin($p));
    }

    public function rechargeFuel(Request $request, ProviderFinanceSrv $srv)
    {
        $p = $request->only('id', 'number', 'app_id');
        $validator = Validator::make($p, [
            'id' => 'present',
            'number' => 'present',
            'app_id' => 'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->rechargeFuel($p));
    }

    public function rechargeReward(Request $request, ProviderFinanceSrv $srv)
    {
        $p = $request->only('id', 'number', 'app_id');
        $validator = Validator::make($p, [
            'id' => 'present',
            'number' => 'present',
            'app_id' => 'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->rechargeReward($p));
    }
}
