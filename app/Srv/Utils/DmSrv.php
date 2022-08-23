<?php

namespace App\Srv\Utils;

include_once 'Dm/aliyun-php-sdk-core/Config.php';

use App\Srv\Srv;
use Dm\Request\V20151123 as Dm;

class DmSrv extends Srv
{
    private $env;
    private $key;
    private $secret;
    private $region;
    private $account;
    private $nickname;

    public function __construct()
    {
        $this->env = env('DM_ENV');
        $this->key = env('DM_KEY');
        $this->secret = env('DM_SECRET');
        $this->region = env('DM_REGION');
        $this->account = env('DM_ACCOUNT');
        $this->nickname = env('DM_NICKNAME');
    }

    private function client()
    {
        $iClientProfile = \DefaultProfile::getProfile($this->region, $this->key, $this->secret);
        return new \DefaultAcsClient($iClientProfile);
    }

    public function sendSingle($to, $tag, $subject, $html)
    {
        if ($this->env == 'local') {
            return $this->returnData();
        }
        $request = new Dm\SingleSendMailRequest();
        $request->setAccountName($this->account);
        $request->setFromAlias($this->nickname);
        $request->setAddressType(1);
        $request->setTagName($tag);
        $request->setReplyToAddress("true");
        $request->setToAddress($to);
        $request->setSubject($subject);
        $request->setHtmlBody($html);
        try {
            $res = $this->client()->getAcsResponse($request);
            if(isset($res->EnvId)){
                return $this->returnData();
            }
            return $this->returnData(ERR_FAILED, '发送失败');
        } catch (\Exception $e) {
            return $this->returnData(ERR_FAILED, '发送失败');
        }
    }
}
