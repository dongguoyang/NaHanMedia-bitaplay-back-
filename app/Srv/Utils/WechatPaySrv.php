<?php

namespace App\Srv\Utils;

use App\Srv\Srv;
use Illuminate\Support\Facades\Http;

class WechatPaySrv extends Srv
{
    private $privatePath;
    private $appId;
    private $mchId;
    private $serialNo;
    private $notifyUrl;
    private $apiKey;

    public function __construct()
    {
        $this->privatePath = storage_path('certs/' . env('WECHAT_PRIVATE_NAME'));
        $this->appId = env('WECHAT_APP_ID');
        $this->mchId = env('WECHAT_MCH_ID');
        $this->serialNo = env('WECHAT_SERIAL_NO');
        $this->notifyUrl = env('WECHAT_NOTIFY_URL');
        $this->apiKey = env('WECHAT_API_KEY');
    }

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

    private function requestSign($method, $url, $time, $nonceStr, $body = '')
    {
        $str = "{$method}\n{$url}\n{$time}\n{$nonceStr}\n{$body}\n";
        return $this->sign($str);
    }

    private function client($sign, $time, $nonceStr)
    {
        $authorization = 'WECHATPAY2-SHA256-RSA2048 ';
        $authorization .= 'signature="' . $sign . '",';
        $authorization .= 'serial_no="' . env('WECHAT_SERIAL_NO') . '",';
        $authorization .= 'timestamp="' . $time . '",';
        $authorization .= 'nonce_str="' . $nonceStr . '",';
        $authorization .= 'mchid="' . env('WECHAT_MCH_ID') . '"';
        return Http::withHeaders([
            'Authorization' => $authorization,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);
    }

    public function nativePay($amount, $localNum, $desc)
    {
        $p['appid'] = $this->appId;
        $p['mchid'] = $this->mchId;
        $p['description'] = $desc;
        $p['out_trade_no'] = $localNum;
        $p['notify_url'] = $this->notifyUrl;
        $p['amount'] = ['total' => $amount];
        $time = time();
        $nonceStr = md5($localNum);
        $sign = $this->requestSign('POST', '/v3/pay/transactions/native', $time, $nonceStr, json_encode($p));
        if (!$sign) {
            return $this->returnData(ERR_FAILED, '支付失败');
        }
        $res = $this->client($sign, $time, $nonceStr)->post('https://api.mch.weixin.qq.com/v3/pay/transactions/native', $p);
        if ($res->status() != 200) {
            return $this->returnData(ERR_FAILED, '支付失败');
        }
        $res = json_decode($res, true);

        return $this->returnData(ERR_SUCCESS, '', ['url' => $res['code_url'], 'number' => $localNum]);
    }

    // 查询订单
    public function check($localNum)
    {
        $timestamp = time();
        $nonceStr = md5($localNum);
        $mchId = $this->mchId;
        $sign = $this->signRequest('GET', "/v3/pay/transactions/out-trade-no/{$localNum}?mchid={$mchId}", $timestamp, $nonceStr, '');
        if (!$sign) {
            return $this->returnData(ERR_FAILED, '支付失败');
        }
        $res = $this->client($sign, $timestamp, $nonceStr)->get("https://api.mch.weixin.qq.com/v3/pay/transactions/out-trade-no/{$localNum}", ['mchid' => $mchId]);
        if ($res->status() != 200) {
            return $this->returnData(ERR_FAILED, '支付失败');
        }
        $res = json_decode($res, true);
        $paymentStatus = PAYMENT_STATUS_PENDING;
        if ($res['trade_state'] == 'SUCCESS') {
            $paymentStatus = PAYMENT_STATUS_ABLE;
        }
        return $this->returnData(ERR_SUCCESS, '', ['payment_number' => $res['transaction_id'], 'status' => $paymentStatus]);
    }

    public function decryptSource($source)
    {
        $nonce = $source['nonce'] ?? '';
        $associatedData = $source['associated_data'] ?? '';
        $cipherText = $source['ciphertext'] ?? '';
        $key = $this->apiKey;
        if ($nonce == '' || $cipherText == '') {
            return $this->returnData(ERR_FAILED, '参数错误');
        }
        $data = sodium_crypto_aead_aes256gcm_decrypt(base64_decode($cipherText), $associatedData, $nonce, $key);
        if (!$data) {
            return $this->returnData(ERR_FAILED, '解密失败');
        }
        return $this->returnData(ERR_SUCCESS, '', json_decode($data, true));
    }
}
