<?php

namespace App\Srv\Utils;

use AlibabaCloud\SDK\Ocrapi\V20210707\Models\RecognizeIdcardRequest;
use AlibabaCloud\SDK\Ocrapi\V20210707\Ocrapi;
use App\Srv\Srv;
use Darabonba\OpenApi\Models\Config;

class RecognizeIdCardSrv extends Srv
{
    private $key;
    private $secret;

    public function __construct()
    {
        $this->key = env('ID_CARD_KEY');
        $this->secret = env('ID_CARD_SECRET');
    }

    private function client()
    {
        $config = new Config([
            "accessKeyId" => $this->key,
            "accessKeySecret" => $this->secret
        ]);
        $config->endpoint = "ocr-api.cn-hangzhou.aliyuncs.com";
        return new Ocrapi($config);
    }

    public function recognize($url)
    {
        try {
            $client = $this->client();
            $recognizeIdCardRequest = new RecognizeIdcardRequest([
                "url" => $url
            ]);
            $res = $client->recognizeIdcard($recognizeIdCardRequest);
            $res = $res->toMap();
            if (isset($res['body']['Data'])) {
                $res = json_decode($res['body']['Data'], true);
                return $this->returnData(ERR_SUCCESS, '', $res['data']);
            } else {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            return $this->returnData(ERR_FAILED, '识别失败');
        }
    }


}
