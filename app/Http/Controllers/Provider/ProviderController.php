<?php


namespace App\Http\Controllers\Provider;


use App\Http\Controllers\Controller;
use App\Srv\Provider\ProviderSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{
    public function register(Request $request, ProviderSrv $srv)
    {
        $p = $request->only('tel', 'code', 'inviter_code');
        $validator = Validator::make($p, [
            'tel' => 'required|size:11',
            'code' => 'required|size:6',
            'inviter_code' => 'present',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->register($p));
    }

    public function login(Request $request, ProviderSrv $srv)
    {
        $p = $request->only('tel', 'code');
        $validator = Validator::make($p, [
            'tel' => 'required|size:11',
            'code' => 'required|size:6',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->login($p));
    }

    public function info(ProviderSrv $srv)
    {
        return $this->responseDirect($srv->info());
    }

    public function bindEmail(Request $request, ProviderSrv $srv)
    {
        $p = $request->only('email', 'code');
        $validator = Validator::make($p, [
            'code' => 'required|size:6',
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->bindEmail($p));
    }

    public function editInfo(Request $request, ProviderSrv $srv)
    {
        $p = $request->only('code', 'name', 'license', 'role', 'id_card_face', 'id_card_back');
        $validator = Validator::make($p, [
            'code' => 'required',
            'name' => 'required',
            'role' => 'required|in:1,2',
            'license' => 'required_if:role,1',
            'id_card_face' => 'required_if:role,2',
            'id_card_back' => 'required_if:role,2',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->editInfo($p));
    }

    public function saveTransPwd(Request $request, ProviderSrv $srv)
    {
        $p = $request->only('code', 'tel', 'pwd');
        $validator = Validator::make($p, [
            'code' => 'required|size:6',
            'tel' => 'required|size:11',
            'pwd' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->saveTransPwd($p));
    }

    public function showTransPwd(Request $request, ProviderSrv $srv)
    {
        $p = $request->only('code', 'tel');
        $validator = Validator::make($p, [
            'code' => 'required|size:6',
            'tel' => 'required|size:11',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->showTransPwd($p));
    }

    public function webLoginFirst(ProviderSrv $srv)
    {
        return $this->responseDirect($srv->webLoginFirst());
    }

    public function webLoginSecond(Request $request, ProviderSrv $srv)
    {
        $data = $request->input('data');
        $appId = $request->input('app_id');
        return $this->responseDirect($srv->webLoginSecond($data, $appId));
    }

    public function webLoginThird(Request $request, ProviderSrv $srv)
    {
        return $this->responseDirect($srv->webLoginThird($request->input('grant_code')));
    }
}
