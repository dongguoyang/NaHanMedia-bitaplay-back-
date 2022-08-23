<?php


namespace App\Srv\Utils;

use App\Srv\Srv;

require_once 'aop/AopCertClient.php';
require_once 'aop/request/AlipayTradePagePayRequest.php';
require_once 'aop/request/AlipayTradeQueryRequest.php';
require_once 'aop/request/AlipayFundTransUniTransferRequest.php';
require_once 'aop/request/AlipayFundTransOrderQueryRequest.php';

class AlipaySrv extends Srv
{
    private $privatePath;
    private $appId;
    private $notifyUrl;
    private $appCertPath;
    private $alipayCertPath;
    private $rootCertPath;
    private $factory;

    public function __construct()
    {
        $this->appId = env('ALIPAY_APP_ID');
        $this->notifyUrl = env('ALIPAY_NOTIFY_URL');
        $this->privatePath = storage_path('certs/alipay_private');
        $this->appCertPath = storage_path('certs/alipay_app_public.crt');
        $this->alipayCertPath = storage_path('certs/alipay_public.crt');
        $this->rootCertPath = storage_path('certs/alipay_root.crt');
        $this->factory = env('ALIPAY_FACTORY');
    }

    private function client()
    {
        $aop = new \AopCertClient();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = str_replace("\n", "", file_get_contents($this->privatePath));
        $aop->alipayrsaPublicKey = $aop->getPublicKey($this->alipayCertPath);
        $aop->isCheckAlipayPublicCert = true;
        $aop->appCertSN = $aop->getCertSN($this->appCertPath);
        $aop->alipayRootCertSN = $aop->getRootCertSN($this->rootCertPath);
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'utf-8';
        $aop->format = 'json';
        return $aop;
    }

    public function nativePay($amount, $localNum, $description)
    {
        $client = $this->client();
        $request = new \AlipayTradePagePayRequest();
        $amount = round($amount / 100, 2);
        $params = "{\"total_amount\":\"{$amount}\",\"out_trade_no\":\"{$localNum}\",\"subject\":\"$description\",\"product_code\":\"FAST_INSTANT_TRADE_PAY\"}";
        $request->setBizContent($params);
        $request->setNotifyUrl($this->notifyUrl);
        $res = $client->pageExecute($request);
        return $this->returnData(ERR_SUCCESS, '', ['number' => $localNum, 'info' => $res]);
    }

    public function check($localNum)
    {
        $client = $this->client();
        $request = new \AlipayTradeQueryRequest();
        $request->setBizContent(json_encode(['out_trade_no' => $localNum]));
        $res = $client->execute($request);
        if (!isset($res->alipay_trade_query_response) || !isset($res->alipay_trade_query_response->code) || $res->alipay_trade_query_response->code != 10000) {
            return $this->returnData(ERR_SUCCESS, '', ['payment_number' => '', 'status' => PAYMENT_STATUS_PENDING]);
        }
        if ($res->alipay_trade_query_response->trade_status == 'TRADE_SUCCESS') {
            return $this->returnData(ERR_SUCCESS, '', ['payment_number' => $res->alipay_trade_query_response->trade_no, 'status' => PAYMENT_STATUS_ABLE]);
        }
        return $this->returnData(ERR_SUCCESS, '', ['payment_number' => '', 'status' => PAYMENT_STATUS_PENDING]);
    }

    public function transToAlipayAccount($amount, $localNum, $account, $name, $title)
    {
        $client = $this->client();
        $request = new \AlipayFundTransUniTransferRequest();
        $amount = round($amount / 100, 2);
        $params = "{\"out_biz_no\":\"{$localNum}\",\"trans_amount\":\"{$amount}\",\"product_code\":\"TRANS_ACCOUNT_NO_PWD\",\"biz_scene\":\"DIRECT_TRANSFER\",\"order_title\":\"{$title}\",\"payee_info\":{\"identity_type\":\"ALIPAY_LOGON_ID\",\"identity\":\"{$account}\",\"name\":\"{$name}\"},\"business_params\":{\"payer_show_name_use_alias\":\"{$this->factory}\"}}";
        $request->setBizContent($params);
        $res = $client->execute($request);
        $res = $res->alipay_fund_trans_uni_transfer_response;
        if ($res->code == "10000" && isset($res->status) && $res->status == 'SUCCESS') {
            return $this->returnData();
        }
        return $this->returnData(ERR_FAILED, '服务器错误');
    }

    public function checkTransToAlipayAccount($localNum)
    {
        $client = $this->client();
        $request = new \AlipayFundTransOrderQueryRequest();
        $request->setBizContent(json_encode(['out_biz_no' => $localNum]));
        $res = $client->execute($request);

        if (!isset($res->alipay_fund_trans_order_query_response)) {
            return $this->returnData(ERR_FAILED, '', ['payment_number' => '', 'status' => WITHDRAW_STATUS_PENDING]);
        }

        $res = $res->alipay_fund_trans_order_query_response;
        if ($res->code != '10000') {
            return $this->returnData(ERR_FAILED, '', ['payment_number' => '', 'status' => WITHDRAW_STATUS_PENDING]);
        }

        if ($res->status == 'SUCCESS') {
            return $this->returnData(ERR_SUCCESS, '', ['payment_number' => $res->order_id, 'status' => WITHDRAW_STATUS_ABLE]);
        }

        if ($res->status == 'FAIL') {
            return $this->returnData(ERR_SUCCESS, '', ['payment_number' => $res->order_id, 'status' => WITHDRAW_STATUS_DISABLE]);
        }

        return $this->returnData(ERR_FAILED, '', ['payment_number' => '', 'status' => WITHDRAW_STATUS_PENDING]);
    }

}

