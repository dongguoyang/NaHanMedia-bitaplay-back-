<?php


namespace App\Http\Controllers\Provider;


use App\Http\Controllers\Controller;
use App\Srv\Provider\AppSrv;
use App\Srv\Provider\WalletSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function index(WalletSrv $srv)
    {
        return $this->responseDirect($srv->index());
    }

    public function recharge(Request $request, WalletSrv $srv)
    {
        $p = $request->only('amount', 'payment_method');
        $validator = Validator::make($p, [
            'amount' => 'required|min:0.01',
            'payment_method' => 'required|in:1,2'
        ]);
        if ($validator->failed()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->recharge($p));
    }

    public function queryRecharge(Request $request, WalletSrv $srv)
    {
        $p = $request->only('number');
        $validator = Validator::make($p, [
            'number' => 'required|size:18',
        ]);
        if ($validator->failed()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->queryRecharge($p['number']));
    }

    public function saveWithdrawAccount(Request $request, WalletSrv $srv)
    {
        $p = $request->only('type', 'bank_name', 'bank_account_name', 'bank_number', 'alipay_account_name', 'alipay_number', 'trans_password');
        $validator = Validator::make($p, [
            'type' => 'required|in:1,2',
            'bank_name' => 'required_if:type,2',
            'bank_account_name' => 'required_if:type,2',
            'bank_number' => 'required_if:type,2',
            'alipay_account_name' => 'required_if:type,1',
            'alipay_number' => 'required_if:type,1',
            'trans_password' => 'required',
        ]);
        if ($validator->failed()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->saveWithdrawAccount($p));
    }

    public function withdrawAccount(WalletSrv $srv)
    {
        return $this->responseDirect($srv->withdrawAccount());
    }

    public function withdraw(Request $request, WalletSrv $srv)
    {
        $p = $request->only('type', 'amount', 'trans_password');
        $validator = Validator::make($p, [
            'type' => 'required|in:1',
            'amount' => 'required|min:0.01',
            'trans_password' => 'required',
        ]);
        if ($validator->failed()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->withdraw($p));
    }

    public function withdrawRecord(Request $request, WalletSrv $srv)
    {
        $p = $request->only('status', 'number');
        $validator = Validator::make($p, [
            'status' => 'present',
            'number' => 'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->withdrawRecord($p));
    }

    public function rechargeRecord(Request $request, WalletSrv $srv)
    {
        $p = $request->only('status', 'number');
        $validator = Validator::make($p, [
            'status' => 'present',
            'number' => 'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->rechargeRecord($p));
    }
}
