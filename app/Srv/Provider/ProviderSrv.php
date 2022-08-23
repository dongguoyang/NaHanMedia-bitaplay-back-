<?php


namespace App\Srv\Provider;


use App\Models\Provider;
use App\Models\ProviderApp;
use App\Models\ProviderInfo;
use App\Models\ProviderWallet;
use App\Models\User;
use App\Srv\Srv;
use App\Srv\Utils\GetBusinessSrv;
use App\Srv\Utils\RecognizeIdCardSrv;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProviderSrv extends Srv
{
    public function register($p)
    {
        // 检查验证码
        $res = (new VerifyCodeSrv())->verifySmsVerifyCode($p['tel'], $p['code'], 3);
        if ($res['code'] != ERR_SUCCESS || !$res['data']) {
            return $this->returnData(ERR_PARAM_ERR, '验证码错误');
        }
        if (Provider::where('tel', $p['tel'])->count() > 0) {
            return $this->returnData(ERR_USER_REGISTERED, '手机号已注册');
        }
        $inviter = null;
        if ($p['inviter_code']) {
            $inviter = User::where('code', $p['inviter_code'])->first();
            if (!$inviter) {
                return $this->returnData(ERR_PARAM_ERR, '邀请码错误');
            }
        }
        try {
            DB::beginTransaction();
            if ($inviter) {
                $p['pid'] = $inviter->id;
            }
            $p['token'] = md5($p['tel']);
            // 注册服务商
            $provider = Provider::create($p);
            // 注册服务商钱包
            ProviderWallet::create(['provider_id' => $provider->id]);
            DB::commit();
            return $this->returnData();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '注册失败');
        }
    }

    public function login($p)
    {
        // 检查验证码
        $res = (new VerifyCodeSrv())->verifySmsVerifyCode($p['tel'], $p['code'], 1);
        if ($res['code'] != ERR_SUCCESS || !$res['data']) {
            return $this->returnData(ERR_PARAM_ERR, '验证码错误');
        }
        $provider = Provider::where('tel', $p['tel'])->first();
        if (!$provider) {
            return $this->returnData(ERR_USER_NOT_REGISTER, '手机号未注册');
        }
        if ($provider->status == PROVIDER_STATUS_DISABLE) {
            return $this->returnData(ERR_USER_DISABLED, '已禁用');
        }
        $provider->token = md5($provider->id . time());
        $provider->save();
        return $this->returnData(ERR_SUCCESS, '', $provider->token);
    }

    public function info()
    {
        $provider = $this->getProvider();
        $info = $provider->info;
        $data['tel'] = $provider->tel;
        $data['email'] = $provider->email;
        $data['name'] = '';
        $data['code'] = '';
        $data['license'] = '';
        $data['status'] = PROVIDER_INFO_STATUS_NOT;
        $data['refuse_reason'] = '';
        $data['role'] =1;
        $data['id_card_face'] = '';
        $data['id_card_back'] = '';
        if ($info) {
            $data['name'] = $info->name;
            $data['code'] = $info->code;
            $data['license'] = $info->license;
            $data['status'] = $info->status;
            $data['refuse_reason'] = $info->refuse_reason;
            $data['id_card_face'] = $info->id_card_face;
            $data['id_card_back'] = $info->id_card_back;
            $data['role'] =$info->role;
        }
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function bindEmail($p)
    {
        // 检查验证码
        $res = (new VerifyCodeSrv())->verifyEmailVerifyCode($p['email'], $p['code'], 1);
        if ($res['code'] != ERR_SUCCESS) {
            return $this->returnData(ERR_PARAM_ERR, '验证码错误');
        }

        $provider = $this->getProvider();
        $provider->email = $p['email'];
        $provider->save();
        return $this->returnData();
    }

    public function editInfo($p)
    {
        $provider = $this->getProvider();
        // 查询企业名称和信用代码是否已用
        $otherInfo = ProviderInfo::where(function ($query) use ($p) {
            $query->where('name', $p['name'])
                ->orWhere('code', $p['code']);
        })->whereIn('status', [
            PROVIDER_INFO_STATUS_PENDING,
            PROVIDER_INFO_STATUS_ABLE
        ])->first();
        if ($otherInfo && $otherInfo->provider_id != $provider->id) {
            return $this->returnData(ERR_PARAM_ERR, '已注册');
        }

        // 本软件商信息
        $info = $provider->info;
        if ($info) {
            if ($info->status == PROVIDER_INFO_STATUS_PENDING) {
                return $this->returnData(ERR_PARAM_ERR, '审核中，请稍后再试');
            }
            // if ($info->status == PROVIDER_INFO_STATUS_ABLE) {
            //     return $this->returnData(ERR_PARAM_ERR, '审核通过，不能再次修改');
            // }
        }
        // 查看信用代码是否正确
        if ($p['role'] == 1) {
            $result = (new GetBusinessSrv())->recognize($p['code']);
            if ($result['code'] != 0 && $result['data'] != $p['name']) {
                return $this->returnData(ERR_PARAM_ERR, '审核失败');
            }
        } else {
            $result = (new RecognizeIdCardSrv())->recognize($p['id_card_face']);
            if ($result['code'] != 0 || $result['data']['face']['data']['name'] != $p['name'] || $result['data']['face']['data']['idNumber'] != $p['code']) {
                return $this->returnData(ERR_PARAM_ERR, '审核失败');
            }
            $p['license'] = '';
        }

        $p['provider_id'] = $provider->id;
        $p['status'] = PROVIDER_INFO_STATUS_ABLE;
        if (!$info) {
            ProviderInfo::create($p);
        } else {
            ProviderInfo::where('id', $info->id)->update($p);
        }
        return $this->returnData();
    }

    public function saveTransPwd($p)
    {
        // 检查验证码
        $res = (new VerifyCodeSrv())->verifySmsVerifyCode($p['tel'], $p['code'], 2);
        if ($res['code'] != ERR_SUCCESS || !$res['data']) {
            return $this->returnData(ERR_PARAM_ERR, '验证码错误');
        }
        $provider = $this->getProvider();
        if ($provider->tel != $p['tel']) {
            return $this->returnData(ERR_PARAM_ERR, '注册手机号不一致');
        }
        $provider->trans_password = $p['pwd'];
        $provider->save();
        return $this->returnData();
    }

    public function showTransPwd($p)
    {
        // 检查验证码
        $res = (new VerifyCodeSrv())->verifySmsVerifyCode($p['tel'], $p['code'], 4);
        if ($res['code'] != ERR_SUCCESS || !$res['data']) {
            return $this->returnData(ERR_PARAM_ERR, '验证码错误');
        }
        $provider = $this->getProvider();
        if ($provider->tel != $p['tel']) {
            return $this->returnData(ERR_PARAM_ERR, '注册手机号不一致');
        }
        return $this->returnData(ERR_SUCCESS, '', $provider->trans_password);
    }

    public function webLoginFirst()
    {
        $grantCode = md5(time() . rand(0, 1000000));
        $appId = "c4ca4238a0b923820dcc509a6f75849b";
        $callbackUrl = env('PROVIDER_DOMAIN') . '/provider/web-login-second';
        Cache::put("pwlf:{$grantCode}", 1, 300);
        return $this->returnData(ERR_SUCCESS, '', ['code' => $grantCode, 'app_id' => $appId, 'callback_url' => $callbackUrl]);
    }

    public function webLoginSecond($data, $appId)
    {
        Log::info("接收到回调：", [$data, $appId]);
        try {
            if (!$app = ProviderApp::where('app_id', $appId)->first()) {
                throw new \Exception('非法操作');
            }
            if (!$data = json_decode($this->decrypt($data, $app->app_secret), true)) {
                throw new \Exception('非法操作');
            }

            $params['data'] = $this->encrypt(json_encode(['code' => $data['code'], 'app_id' => $app->app_id]), $app->app_secret);
            $params['app_key'] = $app->app_key;

            Log::info("查询用户信息：", $params);
            $res = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(env('THIRD_DOMAIN') . '/user/info', $params);
            Log::info("查询用户返回：", [$res->body()]);

            $res = json_decode($res->body(), true);
            if ($res['code'] != 0) {
                throw new \Exception('非法操作');
            }
            if (!$res = json_decode($this->decrypt($res['data'], $app->app_secret), true)) {
                throw new \Exception('非法操作');
            }

            Log::info("用户手机号：", [$res['tel']]);

            Cache::put("pwlf:{$data['grant_code']}", $res['tel'], 300);

        } catch (\Exception $e) {
            return $this->returnData(ERR_PARAM_ERR, '参数错误');
        }
    }


    public function webLoginThird($code)
    {
        if (!$tel = Cache::get("pwlf:{$code}")) {
            return $this->returnData(ERR_EXPIRED, '二维码已过期');
        }
        if ($tel == 1) {
            return $this->returnData();
        }
        if (!$provider = Provider::where('tel', $tel)->first()) {
            return $this->returnData(ERR_EXPIRED, '请注册软件商');
        }
        $provider->token = md5($provider->id . time());
        $provider->save();
        return $this->returnData(ERR_SUCCESS, '', $provider->token);
    }


}

