<?php


namespace App\Srv\Utils;


use AlibabaCloud\Client\AlibabaCloud;
use App\Srv\Srv;
use Illuminate\Support\Facades\Log;

class VerifyPhoneSrv extends Srv
{
    public function verify($token)
    {
        $key = env('OSS_KEY');
        $secret = env('OSS_SECRET');

        AlibabaCloud::accessKeyClient($key, $secret)
            ->regionId('cn-hangzhou')
            ->asDefaultClient();

        try {
            $result = AlibabaCloud::rpc()
                ->product('Dypnsapi')
                ->scheme('https')
                ->version('2017-05-25')
                ->action('GetMobile')
                ->method('POST')
                ->host('dypnsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'AccessToken' => $token,
                    ],
                ])->request();
            $res = $result->toArray();
            if (!isset($res['Code']) || $res['Code'] != 'OK') {
                return $this->returnData(ERR_PARAM_ERR, '登录失败');
            }
            return $this->returnData(ERR_SUCCESS, '', $res['GetMobileResultDTO']['Mobile']);
        } catch (\Exception $e) {
            return $this->returnData(ERR_PARAM_ERR, '登录失败');
        }
    }
}
