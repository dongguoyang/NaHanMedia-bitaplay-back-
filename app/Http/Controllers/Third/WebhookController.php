<?php


namespace App\Http\Controllers\Third;


use App\Http\Controllers\Controller;
use App\Srv\Third\WebhookSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function alipay(Request $request, WebhookSrv $srv)
    {
        $content = $request->all();
        Log::info('提现回调：', $content);
        $content = json_decode($content['biz_content'], true);
        if ($content['product_code'] != 'TRANS_ACCOUNT_NO_PWD') {
            return 'success';
        }
        $number = $content['out_biz_no'];
        $res = $srv->alipay($number);
        if ($res['code'] == 0) {
            return 'success';
        }
        return 'fail';
    }
}
