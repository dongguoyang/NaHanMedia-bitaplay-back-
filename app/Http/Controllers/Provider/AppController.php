<?php


namespace App\Http\Controllers\Provider;


use App\Http\Controllers\Controller;
use App\Srv\Provider\AppSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppController extends Controller
{
    public function list(Request $request, AppSrv $srv)
    {
        $p = $request->only('name', 'status');
        $validator = Validator::make($p, [
            'name' => 'present',
            'status' => 'present'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->list($p));
    }

    public function detail(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->detail($request->input('id')));
    }

    public function create(Request $request, AppSrv $srv)
    {
        $p = $request->only('name', 'desc');
        $validator = Validator::make($p, [
            'name' => 'required',
            'desc' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->create($p));
    }

    public function saveVersion(Request $request, AppSrv $srv)
    {
        $p = $request->only('id', 'provider_app_id', 'version', 'image', 'desc', 'web', 'ios', 'shop', 'is_ios', 'is_android', 'is_web', 'category', 'grade', 'package_name', 'ios_package_name', 'icon', 'banner');
        $validator = Validator::make($p, [
            'id' => 'required|min:0',
            'provider_app_id' => 'required|min:1',
            'version' => 'required',
            'image' => 'required|array|max:9',
            'desc' => 'required',
            'is_web' => 'required|in:0,1',
            'is_ios' => 'required|in:0,1',
            'is_android' => 'required|in:0,1',
            'web' => 'required_if:is_web,1',
            'ios' => 'required_if:is_ios,1',
            'shop' => 'required_if:is_android,1',
            'package_name' => 'required_if:is_android,1',
            'ios_package_name' => 'required_if:is_ios,1',
            'category' => 'required|array|min:1',
            'grade' => 'required|array|min:1',
            'icon' => 'required',
            'banner' => 'required'
        ]);
        if ($validator->fails() || ($p['is_android'] == 1 && (!is_array($p['shop']) || count($p['shop']) == 0))) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->saveVersion($p));
    }

    public function versionList(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->versionList($request->input('status', 0), $request->input('id', 0)));
    }

    public function versionDetail(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->versionDetail($request->input('id', 0)));
    }


    public function editStatus(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->editStatus($request->input('id', 0)));
    }

    public function appDetail(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->appDetail($request->input('id')));
    }

    public function rechargeDownloadReward(Request $request, AppSrv $srv)
    {
        $p = $request->only('amount', 'trans_password', 'id');
        $validator = Validator::make($p, [
            'id' => 'required',
            'amount' => 'required',
            'trans_password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->rechargeDownloadReward($p));
    }

    public function editDownloadReward(Request $request, AppSrv $srv)
    {
        $p = $request->only('amount', 'trans_password', 'id');
        $validator = Validator::make($p, [
            'id' => 'required',
            'amount' => 'required',
            'trans_password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->editDownloadReward($p));
    }

    public function rechargeThirdLogin(Request $request, AppSrv $srv)
    {
        $p = $request->only('amount', 'trans_password', 'id');
        $validator = Validator::make($p, [
            'id' => 'required',
            'amount' => 'required',
            'trans_password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->rechargeThirdLogin($p));
    }


    public function rechargeFuel(Request $request, AppSrv $srv)
    {
        $p = $request->only('amount', 'trans_password', 'id');
        $validator = Validator::make($p, [
            'id' => 'required',
            'amount' => 'required',
            'trans_password' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->rechargeFuel($p));
    }

    public function thirdLoginStatistics(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->thirdLoginStatistics($request->input('id')));
    }

    public function rechargeThirdLoginRecord(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->rechargeThirdLoginRecord($request->input('id')));
    }

    public function consumeThirdLoginRecord(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->consumeThirdLoginRecord($request->input('id')));
    }

    public function fuelStatistics(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->fuelStatistics($request->input('id')));
    }

    public function rechargeFuelRecord(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->rechargeFuelRecord($request->input('id')));
    }

    public function consumeFuelRecord(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->consumeFuelRecord($request->input('id')));
    }

    public function downloadStatistics(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->downloadStatistics($request->input('id')));
    }

    public function rechargeDownloadRecord(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->rechargeDownloadRecord($request->input('id')));
    }

    public function consumeDownloadRecord(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->consumeDownloadRecord($request->input('id')));
    }

    public function recommend(Request $request, AppSrv $srv)
    {
        $p = $request->only('id', 'recommend_city', 'recommend_industry', 'recommend_sex', 'recommend_age', 'recommend_preference', 'recommend_style', 'recommend_educational', 'recommend_device', 'recommend_system', 'recommend_real', 'recommend_week_download', 'recommend_month_download', 'recommend_category_cycle');
        return $this->responseDirect($srv->recommend($p));
    }

    public function remind(Request $request, AppSrv $srv)
    {
        $p = $request->only('id', 'remind_reward', 'remind_third_login', 'remind_fuel');
        return $this->responseDirect($srv->remind($p));
    }

    public function editDownloadRewardStatus(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->editDownloadRewardStatus($request->input('id', 0)));
    }

}
