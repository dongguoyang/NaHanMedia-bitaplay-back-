<?php


namespace App\Http\Controllers\Provider;


use App\Http\Controllers\Controller;
use App\Srv\Provider\WalletSrv;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function alipay(Request $request, WalletSrv $srv)
    {
        $p['number'] = $request->get('out_trade_no');
        $p['payment_method'] = PAYMENT_METHOD_ALIPAY;
        $result = $srv->queryRecharge($p['number']);
        if ($result['code'] != ERR_SUCCESS) {
            return 'success';
        } else {
            return 'fail';
        }
    }
}
