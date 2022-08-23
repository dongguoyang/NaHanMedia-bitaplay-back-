<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Srv\Provider\ToolSrv;
use App\Srv\Provider\VerifyCodeSrv;
use App\Srv\Utils\UploadSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ToolController extends Controller
{
    public function sendSmsVerifyCode(Request $request, VerifyCodeSrv $srv)
    {
        $p = $request->only('tel', 'type');
        $validator = Validator::make($p, [
            'tel' => 'string|required|size:11',
            'type' => 'in:1,2,3,4',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->sendSmsVerifyCode($p['tel'], $p['type']));
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

    public function appCategory(ToolSrv $srv)
    {
        return $this->responseDirect($srv->appCategory());
    }

    public function appGrade(ToolSrv $srv)
    {
        return $this->responseDirect($srv->appGrade());
    }

    public function androidShop(ToolSrv $srv)
    {
        return $this->responseDirect($srv->androidShop());
    }

    public function uploadImage(Request $request, UploadSrv $srv)
    {
        if (!$request->hasFile('file')) {
            return $this->response(ERR_PARAM_ERR, '请上传图片');
        }
        return $this->responseDirect($srv->uploadImage($request->file('file')));
    }

    public function price(ToolSrv $srv)
    {
        return $this->responseDirect($srv->price());
    }

    public function system(ToolSrv $srv)
    {
        return $this->responseDirect($srv->system());
    }

    public function area(ToolSrv $srv)
    {
        return $this->responseDirect($srv->area());
    }

    public function industry(ToolSrv $srv)
    {
        return $this->responseDirect($srv->industry());
    }
}

