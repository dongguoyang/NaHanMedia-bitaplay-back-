<?php

namespace App\Srv\Utils;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use App\Srv\Srv;
use Darabonba\OpenApi\Models\Config;

class SmsSrv extends Srv
{
    private $env;
    private $key;
    private $secret;
    private $signName;
    private $templateCode;
    private $remindProviderCode;

    public function __construct()
    {
        $this->env = env('SMS_ENV');
        $this->key = env('SMS_KEY');
        $this->secret = env('SMS_SECRET');
        $this->signName = env('SMS_SIGN_NAME');
        $this->templateCode = env('SMS_TEMPLATE_CODE');
        $this->remindProviderCode = env('SMS_TEMPLATE_REMIND_PROVIDER');
    }

    private function client()
    {
        $config = new Config([
            'accessKeyId' => $this->key,
            'accessKeySecret' => $this->secret
        ]);
        $config->endpoint = 'dysmsapi.aliyuncs.com';
        return new Dysmsapi($config);
    }

    public function sendCode($tel, $code)
    {
        if ($this->env == 'local') {
            return $this->returnData();
        }

        $client = $this->client();
        $sendSmsRequest = new SendSmsRequest([
            'phoneNumbers' => $tel,
            'signName' => $this->signName,
            'templateCode' => $this->templateCode,
            'templateParam' => json_encode(['code' => $code])
        ]);
        try {
            $res = $client->sendSms($sendSmsRequest);
            $res = $res->toMap();
            if ($res['body']['Code'] != 'OK') {
                throw new \Exception();
            }
            return $this->returnData();
        } catch (\Exception $e) {
            return $this->returnData(ERR_FAILED, '发送失败');
        }
    }

    public function sendRemindProvider($tel, $name, $function, $amount)
    {
        if ($this->env == 'local') {
            return $this->returnData();
        }
        $client = $this->client();
        $sendSmsRequest = new SendSmsRequest([
            'phoneNumbers' => $tel,
            'signName' => $this->signName,
            'templateCode' => $this->remindProviderCode,
            'templateParam' => json_encode(['name' => $name, 'function' => $function, 'amount' => $amount])
        ]);
        try {
            $res = $client->sendSms($sendSmsRequest);
            $res = $res->toMap();
            if ($res['body']['Code'] != 'OK') {
                throw new \Exception();
            }
            return $this->returnData();
        } catch (\Exception $e) {
            return $this->returnData(ERR_FAILED, '发送失败');
        }

    }
}
