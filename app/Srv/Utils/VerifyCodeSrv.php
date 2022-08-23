<?php


namespace App\Srv\Utils;


use App\Srv\Srv;
use Illuminate\Support\Facades\Cache;

class VerifyCodeSrv extends Srv
{
    private $env;

    public function __construct()
    {
        $this->env = env('VERIFY_CODE_ENV');
    }

    public function createCode($len = 6)
    {
        $strArr = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $code = '';
        for ($i = 0; $i < $len; $i++) {
            $code .= $strArr[rand(0, 9)];
        }
        return $code;
    }

    public function sendSmsVerifyCode($tel, $cacheKey)
    {
        $code = $this->createCode();

        // 缓存验证码
        Cache::put($cacheKey, $code, 300);

        // 发送验证码
        $res = (new SmsSrv())->sendCode($tel, $code);
        if ($res['code'] != ERR_SUCCESS) {
            return $res;
        }

        return $this->returnData(ERR_SUCCESS, '', '');
    }

    public function sendEmailVerifyCode($email, $cacheKey)
    {
        $code = $this->createCode();

        // 缓存验证码
        Cache::put($cacheKey, $code, 300);

        $html = "<p>【Bitaplay】</p> 您的验证码是<strong>{$code}</strong>。请在5分钟内按照页面提示提交验证码。不要向他人透露验证码！";


        // 发送验证码
        $res = (new DmSrv())->sendSingle($email, env('DM_VERIFY_TAG'), 'Bitaplay验证码', $html);
        if ($res['code'] != ERR_SUCCESS) {
            return $res;
        }

        return $this->returnData(ERR_SUCCESS, '', '');
    }

    public function verifyCode($cacheKey, $code)
    {
        if ($this->env == 'local') {
            return $this->returnData(ERR_SUCCESS, '', true);
        }

        $cacheCode = Cache::get($cacheKey);

        if ($cacheCode == $code) {
            return $this->returnData(ERR_SUCCESS, '', true);
        }

        return $this->returnData(ERR_SUCCESS, '', false);
    }
}
