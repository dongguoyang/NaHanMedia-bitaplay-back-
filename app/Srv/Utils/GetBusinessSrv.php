<?php

namespace App\Srv\Utils;

use AlibabaCloud\SDK\Ocrapi\V20210707\Models\RecognizeIdcardRequest;
use AlibabaCloud\SDK\Ocrapi\V20210707\Ocrapi;
use App\Srv\Srv;
use Darabonba\OpenApi\Models\Config;
use Illuminate\Support\Facades\Http;

class GetBusinessSrv extends Srv
{
    private $code;

    public function __construct()
    {
        $this->code = env('BUSINESS_CODE');
    }

    public function recognize($code)
    {
        try {
            $res = Http::withHeaders(['Authorization' => "APPCODE {$this->code}"])
                ->get('https://businessstd.shumaidata.com/getbusinessstd', ['keyword' => urlencode($code)]);
            if ($res->status() != 200) {
                throw new \Exception();
            }
            $res = json_decode($res->body(), true);
            if (!$res['success']) {
                throw new \Exception('失败失败');
            }
            return $this->returnData(ERR_SUCCESS, '', $res['data']['data']['companyName']);
        } catch (\Exception $e) {
            return $this->returnData(ERR_FAILED, '识别失败');
        }
    }


}
