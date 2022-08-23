<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Srv\Api\ToolSrv;
use App\Srv\Api\VerifyCodeSrv;
use App\Srv\Utils\UploadSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ToolController extends Controller
{
    public function sendSmsVerifyCode(Request $request, VerifyCodeSrv $srv)
    {
        $p = $request->only('tel', 'type');
        $validator = Validator::make($p, [
            'tel' => 'string|required|size:11',
            'type' => 'in:1,2,3',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->sendSmsVerifyCode($p['tel'], $p['type']));
    }

    public function sts(UploadSrv $srv)
    {
        return $this->responseDirect($srv->sts());
    }

    public function industry(ToolSrv $srv)
    {
        return $this->responseDirect($srv->industry());
    }

    public function occupation(Request $request, ToolSrv $srv)
    {
        $industryId = $request->input('industry_id', 0);
        return $this->responseDirect($srv->occupation($industryId));
    }

    public function sendEmailVerifyCode(Request $request, VerifyCodeSrv $srv)
    {
        $p = $request->only('email', 'type');
        $validator = Validator::make($p, [
            'email' => 'required|email',
            'type' => 'in:1',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->sendEmailVerifyCode($p['email'], $p['type']));
    }

    public function area(ToolSrv $srv)
    {
        return $this->responseDirect($srv->area());
    }

    public function appCategory(Request $request, ToolSrv $srv)
    {
        $oaid = $request->header('User-Agent');
//        Log::info("agent:{$oaid}");
        $oaid = explode('&',$oaid);
        $oaid = explode('=',$oaid[0]);
        return $this->responseDirect($srv->appCategory($oaid[1]));
    }


    public function system(ToolSrv $srv)
    {
        return $this->responseDirect($srv->system());
    }

    public function agreement(ToolSrv $srv)
    {
        return $this->responseDirect($srv->agreement());
    }

    public function hotWord(ToolSrv $srv)
    {
        return $this->responseDirect($srv->hotWord());
    }

    public function avatar(ToolSrv $srv)
    {
        return $this->responseDirect($srv->avatar());
    }

    // 数据抓取
    public function grap(Request $request, ToolSrv $srv)
    {
        return $this->responseDirect($srv->grap());
    }

    // 更新下载量
    public function updateDownload(ToolSrv $srv)
    {
        return $this->responseDirect($srv->updateDownload());
    }

    // 广告监测
    public function kuaishouAds(Request $request, ToolSrv $srv)
    {
        $params = $request->only('account_id', 'aid', 'cid', 'did', 'ts', 'ip', 'callback', 'oaid','oaid2');
        return $this->responseDirect($srv->kuaishouAds($params));
    }
}

