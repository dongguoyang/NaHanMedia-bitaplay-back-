<?php


namespace App\Srv\Provider;


use App\Srv\Srv;

class VerifyCodeSrv extends Srv
{
    private function getSmsCacheKey($tel, $type)
    {
        $cacheKey = 'sms:provider:';
        switch ($type) {
            case 1: // 登录
                $cacheKey .= "login:{$tel}";
                break;
            case 2: // 修改支付密码
                $cacheKey .= "tp:{$tel}";
                break;
            case 3: // 注册
                $cacheKey .= "register:{$tel}";
                break;
            case 4: // 查看支付密码
                $cacheKey .= "stp:{$tel}";
                break;
        }
        return $cacheKey;
    }

    public function sendSmsVerifyCode($tel, $type)
    {
        $cacheKey = $this->getSmsCacheKey($tel, $type);
        return (new \App\Srv\Utils\VerifyCodeSrv())->sendSmsVerifyCode($tel, $cacheKey);
    }

    public function verifySmsVerifyCode($tel, $code, $type)
    {
        $cacheKey = $this->getSmsCacheKey($tel, $type);
        return (new \App\Srv\Utils\VerifyCodeSrv())->verifyCode($cacheKey, $code);
    }

    private function getEmailCacheKey($email, $type)
    {
        $cacheKey = 'dm:provider:';
        switch ($type) {
            case 1: // 登录
                $cacheKey .= "bind:{$email}";
                break;
        }
        return $cacheKey;
    }

    public function sendEmailVerifyCode($email, $type)
    {
        $cacheKey = $this->getEmailCacheKey($email, $type);
        return (new \App\Srv\Utils\VerifyCodeSrv())->sendEmailVerifyCode($email, $cacheKey);
    }

    public function verifyEmailVerifyCode($email, $code, $type)
    {
        $cacheKey = $this->getEmailCacheKey($email, $type);
        return (new \App\Srv\Utils\VerifyCodeSrv())->verifyCode($cacheKey, $code);
    }
}
