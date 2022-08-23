<?php


namespace App\Srv\Provider;


use App\Srv\Utils\AlipaySrv;
use App\Srv\Srv;
use App\Srv\Utils\WechatPaySrv;

class PaySrv extends Srv
{
    private $obj;
    private $method;
    private $env;

    public function __construct($paymentMethod)
    {
        $this->method = $paymentMethod;
        if ($paymentMethod == PAYMENT_METHOD_WECHAT) {
            $this->obj = new WechatPaySrv();
        } else {
            $this->obj = new AlipaySrv();
        }
        $this->env = env('PAY_ENV');
    }

    public function pay($amount, $localNum, $desc)
    {
        if ($this->env == 'local') {
            return $this->mockPay($localNum);
        }
        return $this->obj->nativePay($amount, $localNum, $desc);
    }

    public function check($localNum)
    {
        if ($this->env == 'local') {
            return $this->mockCheck();
        }
        return $this->obj->check($localNum);
    }

    public function mockPay($localNum)
    {
        $returnData['number'] = $localNum;
        if ($this->method == PAYMENT_METHOD_WECHAT) {
            $returnData['info']['appid'] = 'appid';
            $returnData['info']['partnerid'] = 'partnerid';
            $returnData['info']['prepayid'] = 'prepayid';
            $returnData['info']['package'] = 'Sign=WXPay';
            $returnData['info']['noncestr'] = 'noncestr';
            $returnData['info']['timestamp'] = time();
            $returnData['info']['sign'] = 'sign';
        } else {
            $returnData['info'] = 'sign';
        }
        return $this->returnData(ERR_SUCCESS, '', $returnData);
    }

    public function mockCheck()
    {
        return $this->returnData(ERR_SUCCESS, '', ['payment_number' => md5(time()), 'status' => PAYMENT_STATUS_ABLE]);
    }
}

