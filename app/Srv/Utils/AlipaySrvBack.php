<?php


namespace App\Srv\Utils;

use App\Srv\Srv;
use Illuminate\Support\Facades\Http;


class AlipaySrvBack extends Srv
{
    private $privatePath;
    private $appId;
    private $notifyUrl;

    public function __construct()
    {
        $this->appId = env('ALIPAY_APP_ID');
        $this->notifyUrl = env('ALIPAY_NOTIFY_URL');
        $this->privatePath = storage_path('certs/alipay_private');
    }

    // 签名
    private function sign($str)
    {
        // 获取私钥
        $privateKey = openssl_pkey_get_private(file_get_contents($this->privatePath));
        if (!$privateKey) {
            return false;
        }

        // 开始签名
        if (!openssl_sign($str, $sign, $privateKey, 'sha256WithRSAEncryption')) {
            return false;
        }
        return base64_encode($sign);
    }

    // 签名字符串构造
    private function createSignStr($p, $encode = false)
    {
        ksort($p);
        $str = '';
        foreach ($p as $k => $v) {
            if ($k == 'biz_content' && $encode) {
                $v = urlencode($v);
            }
            $str .= "{$k}=$v&";
        }
        return rtrim($str, "&");
    }

    public function nativePay($amount, $localNum, $description)
    {
        $amount = round($amount / 100, 2);
        $params = [
            'app_id' => $this->appId,
            'method' => 'alipay.trade.app.pay',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => $this->notifyUrl,
            'qr_pay_mode' => 1,
            'biz_content' => json_encode([
                'out_trade_no' => $localNum,
                'total_amount' => $amount,
                'subject' => $description,
            ]),
        ];
        $signStr = $this->createSignStr($params);
        if (!$sign = $this->sign($signStr)) {
            return $this->returnData(ERR_FAILED, '支付失败');
        }
        $params['sign'] = $sign;
        $res = Http::get('https://openapi.alipay.com/gateway.do', $params);
        $body = mb_convert_encoding($res->body(), 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        return $this->returnData(ERR_SUCCESS, '', ['number' => $localNum, 'info' => $body]);
    }

    // 查询订单
    public function check($localNum)
    {
        $params = [
            'app_id' => $this->appId,
            'method' => 'alipay.trade.query',
            'charset' => 'utf-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
            'notify_url' => $this->notifyUrl,
            'biz_content' => json_encode([
                'out_trade_no' => $localNum,
            ]),
        ];
        $signStr = $this->createSignStr($params);
        if (!$sign = $this->sign($signStr)) {
            return $this->returnData(ERR_FAILED, '查询失败');
        }
        $params['sign'] = $sign;
        $res = Http::get('https://openapi.alipay.com/gateway.do', $params);
        $res = json_decode($res, true);
        if ($res['alipay_trade_query_response']['code'] != 10000) {
            return $this->returnData(ERR_FAILED, '查询失败');
        }
        $paymentStatus = BILL_PAYMENT_STATUS_NOT;
        if ($res['alipay_trade_query_response']['trade_status'] == 'TRADE_CLOSED') {
            $paymentStatus = BILL_PAYMENT_STATUS_CANCEL;
        } elseif ($res['alipay_trade_query_response']['trade_status'] == 'TRADE_SUCCESS') {
            $paymentStatus = BILL_PAYMENT_STATUS_SUCCESS;
        }
        return $this->returnData(ERR_SUCCESS, '', ['payment_number' => $res['alipay_trade_query_response']['trade_no'], 'status' => $paymentStatus]);
    }
}

