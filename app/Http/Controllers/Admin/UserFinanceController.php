<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\UserFinanceSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserFinanceController extends Controller
{
    public function withdraw(Request $request, UserFinanceSrv $srv)
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


    public function doWithdraw(Request $request, UserFinanceSrv $srv)
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

    public function downloadReward(Request $request, UserFinanceSrv $srv)
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

        return $this->responseDirect($srv->downloadReward($p));
    }

    public function recommendReward(Request $request, UserFinanceSrv $srv)
    {
        $p = $request->only('id', 'number', 'tel');
        $validator = Validator::make($p, [
            'id' => 'present',
            'number' => 'present',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->recommendReward($p));

    }
}
