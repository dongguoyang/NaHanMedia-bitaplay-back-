<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Srv\Api\UserWalletSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    public function info(UserWalletSrv $srv)
    {
        return $this->responseDirect($srv->info());
    }

    public function saveWithdrawAccount(Request $request, UserWalletSrv $srv)
    {
        $p = $request->only('bank_name', 'bank_account_name', 'bank_number', 'alipay_account_name', 'alipay_number', 'type', 'trans_password');
        $validator = Validator::make($p, [
            'bank_name' => 'required_if:type,2',
            'bank_account_name' => 'required_if:type,2',
            'bank_number' => 'required_if:type,2',
            'alipay_account_name' => 'required_if:type,1',
            'alipay_number' => 'required_if:type,1',
            'type' => 'in:1,2',
            'trans_password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->saveWithdrawAccount($p));
    }

    public function withdrawAccount(UserWalletSrv $srv)
    {
        return $this->responseDirect($srv->withdrawAccount());
    }

    public function withdraw(Request $request, UserWalletSrv $srv)
    {
        $p = $request->only('amount', 'type', 'trans_password');
        $validator = Validator::make($p, [
            'amount' => 'required',
            'type' => 'in:1',
            'trans_password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }

        return $this->responseDirect($srv->withdraw($p));
    }


    public function records(Request $request, UserWalletSrv $srv)
    {
        $p = $request->only('type');
        $validator = Validator::make($p, [
            'type' => 'required|in:0,1,2,3,4,5',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->records($p));
    }
}


