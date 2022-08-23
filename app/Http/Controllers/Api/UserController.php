<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Srv\Api\UserSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login(Request $request, UserSrv $srv)
    {
        $p = $request->only('tel', 'code', 'device', 'oaid');
        $validator = Validator::make($p, [
            'tel' => 'required|size:11',
            'code' => 'required|size:6',
            'device' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        $oaid = $request->header('User-Agent');
        $oaid = explode('&', $oaid);
        $oaid = explode('=', $oaid[0]);
        $p['oaid'] = $oaid[1];
        return $this->responseDirect($srv->login($p));
    }

    public function cancel(UserSrv $srv)
    {
        return $this->responseDirect($srv->cancel());
    }

    public function editTel(Request $request, UserSrv $srv)
    {
        $p = $request->only('tel', 'code');
        $validator = Validator::make($p, [
            'tel' => 'required|size:11',
            'code' => 'required|size:6',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->editTel($p));
    }

    public function oneClickLogin(Request $r, UserSrv $srv)
    {
        $p = $r->only('access_token', 'device');
        $validator = Validator::make($p, [
            'access_token' => 'required',
            'device' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '请输入完整信息');
        }
        return $this->responseDirect($srv->oneClickLogin($p));
    }

    public function basicInfo(UserSrv $srv)
    {
        return $this->responseDirect($srv->basicInfo());
    }

    public function editBasicInfo(Request $request, UserSrv $srv)
    {
        $p = $request->only('nickname', 'avatar', 'desc');
        $validator = Validator::make($p, [
            'nickname' => 'required',
            'avatar' => 'required',
            'desc' => 'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->editBasicInfo($p));
    }

    public function bindEmail(Request $request, UserSrv $srv)
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

    public function otherInfo(UserSrv $srv)
    {
        return $this->responseDirect($srv->otherInfo());
    }

    public function editOtherInfo(Request $request, UserSrv $srv)
    {
        $p['province'] = $request->input('province', '');
        $p['city'] = $request->input('city', '');
        $p['county'] = $request->input('county', '');
        $p['industry_id'] = $request->input('industry_id', 0);
        $p['occupation_id'] = $request->input('occupation_id', 0);
        $p['educational_experience'] = $request->input('educational_experience', []);
        $p['address'] = $request->input('address', []);
        return $this->responseDirect($srv->editOtherInfo($p));
    }

    public function thirdAuth(Request $request, UserSrv $srv)
    {
        $appId = $request->input('app_id');
        return $this->responseDirect($srv->thirdAuth($appId));
    }

    public function webAuth(Request $request, UserSrv $srv)
    {
        $data = $request->input('data');
        return $this->responseDirect($srv->webAuth($data));
    }

    public function uploadIdCard(Request $request, UserSrv $srv)
    {
        $p = $request->only('face', 'back');
        $validator = Validator::make($p, [
            'face' => 'required|url',
            'back' => 'required|url',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->uploadIdCard($p));
    }

    public function editInfoStatus(UserSrv $srv)
    {
        return $this->responseDirect($srv->editInfoStatus());
    }

    public function editAdStatus(Request $request, UserSrv $srv)
    {
        return $this->responseDirect($srv->editAdStatus($request->input('did', '')));
    }

    public function editTransPassword(Request $request, UserSrv $srv)
    {
        $p = $request->only('tel', 'code', 'trans_password');
        $validator = Validator::make($p, [
            'tel' => 'required|size:11',
            'code' => 'required|size:6',
            'trans_password' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }

        return $this->responseDirect($srv->editTransPassword($p));
    }

    public function inviterRecords(UserSrv $srv)
    {
        return $this->responseDirect($srv->inviterRecords());
    }

    public function grantRecord(UserSrv $srv)
    {
        return $this->responseDirect($srv->grantRecord());
    }

    public function toChain(UserSrv $srv)
    {
        return $this->responseDirect($srv->toChain());
    }

    public function createDid(UserSrv $srv)
    {
        return $this->responseDirect($srv->createDid());
    }

    public function otherInfoToChain(Request $request, UserSrv $srv)
    {
        $p['province'] = $request->input('province', '');
        $p['city'] = $request->input('city', '');
        $p['county'] = $request->input('county', '');
        $p['industry_id'] = $request->input('industry_id', 0);
        $p['occupation_id'] = $request->input('occupation_id', 0);
        $p['educational_experience'] = $request->input('educational_experience', []);
        $p['address'] = $request->input('address', []);
        return $this->responseDirect($srv->otherInfoToChain($p));
    }

    public function feedback(Request $request, UserSrv $srv)
    {
        $p = $request->only('content', 'image');
        $validator = Validator::make($p, [
            'content' => 'required',
            'image' => 'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->feedback($p));
    }

    public function persona(UserSrv $srv)
    {
        return $this->responseDirect($srv->persona());
    }

    public function idCardLogin(Request $request, UserSrv $srv)
    {
        $p = $request->only('tel', 'name', 'id_number', 'device');
        $validator = Validator::make($p, [
            'tel' => 'required|size:11',
            'name' => 'required',
            'id_number' => 'required|size:18',
            'device' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->idCardLogin($p));
    }

    public function openAdStatusInfo(UserSrv $srv)
    {
        return $this->responseDirect($srv->openAdStatusInfo());
    }
}
