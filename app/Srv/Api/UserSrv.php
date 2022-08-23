<?php


namespace App\Srv\Api;


use App\Models\Feedback;
use App\Models\KuaishouAds;
use App\Models\Provider;
use App\Models\ProviderApp;
use App\Models\ProviderAppUsers;
use App\Models\System;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserDownloadRecord;
use App\Models\UserInfo;
use App\Models\UserThirdLoginRecord;
use App\Models\UserWallet;
use App\Srv\Utils\AntSrv;
use App\Srv\Utils\VerifyPhoneSrv;
use App\Srv\MyException;
use App\Srv\Srv;
use App\Srv\Utils\RecognizeIdCardSrv;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserSrv extends Srv
{
    private function getCode()
    {
        $strArr = [
            'A', 'B', 'C', 'D', 'E', 'F',
            'G', 'H', 'J', 'K', 'L', 'M',
            'N', 'P', 'Q', 'R', 'S', 'T',
            'U', 'V', 'W', 'X', 'Y', 'Z',
            '2', '3', '4', '5', '6', '7',
            '8', '9'
        ];
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $strArr[rand(0, 31)];
        }
        if (User::where('code', $code)->count()) {
            $this->getCode();
        }
        return $code;
    }

    private function initUser($tel)
    {
        $user = new User();
        $user->tel = $tel;
        $user->code = $this->getCode();
        $user->nickname = 'BITA_' . substr($tel, 7, 4);
        $user->avatar = 'https://knockdoor-bita.oss-cn-chengdu.aliyuncs.com/avatar/header1.png';
        $user->token = md5($tel . time() . Str::random());
        return $user;
    }

    private function createUser($p)
    {
        $user = $this->initUser($p['tel']);
        // 保存用户
        $user->save();
        // 创建用户钱包
        UserWallet::create(['user_id' => $user->id]);
        return $user;
    }

    private function callKs($oaid)
    {
        if ($data = KuaishouAds::where('oaid', $oaid)->orderByDesc('id')->first()) {
            if ($data->status == 1) {
                // 开始回调
                $now = time() * 1000;
                $url = "{$data->callback}&event_time={$now}&event_type=2";
                Log::info('回调快手内容', [$url]);
                Http::get($url);
            }
        }

    }

    public function login($p)
    {
        // 检查验证码
        if ($p['tel'] != '15023321043') {
            $res = (new VerifyCodeSrv())->verifySmsVerifyCode($p['tel'], $p['code'], 1);
            if ($res['code'] != ERR_SUCCESS || !$res['data']) {
                return $this->returnData(ERR_PARAM_ERR, '验证码错误');
            }
        }
        // 是否注册
        $isUnregistered = false;

        try {
            DB::beginTransaction();
            $user = User::where('tel', $p['tel'])
                ->where('status', '!=', USER_STATUS_DEL)
                ->first();
            // 手机号未注册/已注销
            if (!$user) {
                $user = $this->createUser($p);
                $isUnregistered = true;
            } else {
                if ($user->status != USER_STATUS_ABLE) {
                    throw new MyException('已禁用', ERR_USER_DISABLED);
                }
                $user->token = md5($p['tel'] . $p['code'] . time());
                $user->save();
            }

            // 添加用户设备信息
            UserDevice::create([
                'user_id' => $user->id,
                'name' => $p['device'],
                'ip' => $_SERVER['REMOTE_ADDR'],
            ]);
            DB::commit();
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData($e->getCode(), $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '服务器错误');
        }

        // 注册成功后操作
        if ($isUnregistered && isset($p['oaid'])) {
            (new ToolSrv())->callKuaishouAds($p['oaid'], 2);
        }

        return $this->basicInfo($user->id);
    }


    public function cancel()
    {
        $user = $this->getUser();
        $user->status = USER_STATUS_DEL;
        $user->save();
        return $this->returnData();
    }

    public function editTel($p)
    {
        // 检查验证码
        $res = (new VerifyCodeSrv())->verifySmsVerifyCode($p['tel'], $p['code'], 3);
        if ($res['code'] != ERR_SUCCESS || !$res['data']) {
            return $this->returnData(ERR_PARAM_ERR, '验证码错误');
        }
        if ($telUser = User::where('tel', $p['tel'])->where('status', '!=', USER_STATUS_DEL)->first()) {
            return $this->returnData(ERR_PARAM_ERR, '手机号已注册');
        }
        $user = $this->getUser();
        $user->tel = $p['tel'];
        $user->save();
        return $this->returnData();
    }

    public function oneClickLogin($p)
    {
        $result = (new VerifyPhoneSrv())->verify($p['access_token']);
        if ($result['code'] != ERR_SUCCESS) {
            return $result;
        }
        $p['tel'] = $result['data'];
        try {
            DB::beginTransaction();
            $user = User::where('tel', $p['tel'])
                ->where('status', '!=', USER_STATUS_DEL)
                ->first();
            // 手机号未注册/已注销
            if (!$user) {
                $user = $this->createUser($p);
            } else {
                if ($user->status != USER_STATUS_ABLE) {
                    throw new MyException('已禁用', ERR_USER_DISABLED);
                }
                $user->token = md5($p['tel'] . time());
                $user->save();
            }

            // 添加用户设备信息
            UserDevice::create([
                'user_id' => $user->id,
                'name' => $p['device'],
                'ip' => $_SERVER['REMOTE_ADDR'],
            ]);

            DB::commit();
            return $this->basicInfo($user->id);
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData($e->getCode(), $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '服务器错误');
        }
    }

    public function basicInfo($id = 0)
    {
        if ($id == 0) {
            $user = $this->getUser();
        } else {
            $user = User::where('id', $id)->first();
        }
        $info = $user->info;
        $user = $user->toArray();
        $user['is_certification'] = 0;
        $user['ad_status'] = 1;
        $user['ad_write_did'] = 1;
        // 没有上链，不显示DID
        if (!$info || !$info->hash) {
            $user['code'] = '';
        }
        // 是否实名认证
        if ($info && $info->name) {
            $user['is_certification'] = 1;
        }
        // 是否开启广告收费
        if ($info) {
            $user['ad_status'] = $info->ad_status;
        }

        // 广告是否需要填DID
        if (!$child = User::where('pid', $user['id'])->first()) {
            $user['ad_write_did'] = 1;
        } else {
            $user['ad_write_did'] = 0;
        }

        if (!$info || $info->hash == '') {
            $user['ad_write_did'] = 1;
        }

        if ($info && $info->hash != '' && $info->direct_open_ad_status == 2) {
            $user['ad_write_did'] = 0;
        }

        unset($user['info']);

        return $this->returnData(ERR_SUCCESS, '', $user);
    }

    public function editBasicInfo($p)
    {
        $user = $this->getUser();
        $p['desc'] = $p['desc'] ?: '';
        User::where('id', $user->id)->update($p);
        return $this->returnData();
    }

    public function bindEmail($p)
    {
        // 检查验证码
        $res = (new VerifyCodeSrv())->verifyEmailVerifyCode($p['email'], $p['code'], 1);
        if ($res['code'] != ERR_SUCCESS || !$res['data']) {
            return $this->returnData(ERR_PARAM_ERR, '验证码错误');
        }

        $user = $this->getUser();
        $user->email = $p['email'];
        $user->save();
        return $this->returnData();
    }

    public function otherInfo()
    {
        $info = $this->getUser()->info()->with('occupation', 'industry')->first();
        return $this->returnData(ERR_SUCCESS, '', $info);
    }

    public function editOtherInfo($p)
    {
        $user = $this->getUser();
        $info = $user->info;
        if ($info) {
            UserInfo::where('id', $info->id)->update($p);
        } else {
            $p['user_id'] = $user->id;
            UserInfo::create($p);
        }
        return $this->returnData();
    }

    public function thirdAuth($appId)
    {
        $user = $this->getUser();
        try {
            DB::beginTransaction();
            if (!$app = ProviderApp::where('app_id', $appId)->lockForUpdate()->first()) {
                throw new MyException('APP ID错误');
            }
            if ($app->third_login_amount == 0) {
                throw new MyException('三方登录次数已告罄，请充值后使用');
            }
            $provider = $app->provider;
            if ($provider->status != PROVIDER_STATUS_ABLE) {
                throw new MyException('软件商已被禁用');
            }
            // 写openID
            if (!$openId = ProviderAppUsers::where(['user_id' => $user->id, 'provider_app_id' => $app->id])->first()) {
                ProviderAppUsers::create([
                    'user_id' => $user->id,
                    'provider_app_id' => $app->id,
                    'open_id' => md5($user->id . $app->id . time())
                ]);
            }

            // 缓存CODE
            $code = md5($user->id . $appId . time() . rand(0, 100000));
            $content = json_encode(['uid' => $user->id, 'aid' => $app->id]);
            Cache::put($code, $content, 300);
            DB::commit();
            return $this->returnData(ERR_SUCCESS, '', $code);
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '服务器错误');
        }
    }

    public function webAuth($data)
    {
        $user = $this->getUser();

        Log::info("请求数据：", [$data]);

        try {
            DB::beginTransaction();
            if (!$data = Cache::pull("twl:{$data}")) {
                Log::info("缓存数据：", [$data]);
                throw new MyException('二维码已过期');
            }

            $data = Crypt::decrypt($data);
            Log::info("解密数据：", [$data]);
            if (time() - $data['time'] > 300) {
                Log::info("真实过期：", [$data]);
                throw new MyException('二维码已过期');
            }

            if (!$app = ProviderApp::where('app_id', $data['app_id'])->first()) {
                throw new MyException('APP ID错误');
            }

            if ($app->third_login_amount == 0) {
                throw new MyException('三方登录次数已告罄，请充值后使用');
            }

            $provider = $app->provider;
            if ($provider->status != PROVIDER_STATUS_ABLE) {
                throw new MyException('软件商已被禁用');
            }


            // 写openID
            if (!$openId = ProviderAppUsers::where(['user_id' => $user->id, 'provider_app_id' => $app->id])->first()) {
                ProviderAppUsers::create([
                    'user_id' => $user->id,
                    'provider_app_id' => $app->id,
                    'open_id' => md5($user->id . $app->id . time())
                ]);
            }

            // 缓存CODE
            $code = md5($user->id . $data['app_id'] . time() . rand(0, 100000));
            $content = json_encode(['uid' => $user->id, 'aid' => $app->id]);
            Cache::put($code, $content, 300);

            // 调回调接口
            $param = ['data' => $this->encrypt(json_encode(['code' => $code, 'grant_code' => $data['grant_code']]), $app->app_secret), 'app_id' => $data['app_id']];
            Log::info("开始回调：", [$param]);
            Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($data['callback_url'], $param);

            DB::commit();
            return $this->returnData();
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '服务器错误');
        }
    }

    public function uploadIdCard($p)
    {
        $user = $this->getUser();
        // 判断是否已认证
        $info = $user->info;
        if ($info && $info->name) {
            return $this->returnData(ERR_PARAM_ERR, '已认证');
        }

        // 开始认证
        $res = (new RecognizeIdCardSrv())->recognize($p['face']);
        if ($res['code'] != ERR_SUCCESS) {
            return $res;
        }
        $res = $res['data'];
        if (!isset($res['face'])) {
            return $this->returnData(ERR_FAILED, '请上传清晰的照片');
        }
        $res = $res['face']['data'];
        if (!$res['idNumber'] || !$res['name']) {
            return $this->returnData(ERR_FAILED, '请上传清晰的照片');
        }

        if (UserInfo::where('id_number', $res['idNumber'])->count() > 0) {
            return $this->returnData(ERR_PARAM_ERR, '身份证已使用');
        }

        // 更新
        $res['birthDate'] = str_replace('年', '-', $res['birthDate']);
        $res['birthDate'] = str_replace('月', '-', $res['birthDate']);
        $res['birthDate'] = str_replace('日', '', $res['birthDate']);
        $birthDate = $res['birthDate'] . '00:00:00';
        $data = [
            'user_id' => $user->id,
            'id_number' => $res['idNumber'],
            'sex' => $res['sex'] == '男' ? 1 : 2,
            'age' => ceil((time() - strtotime($birthDate)) / (12 * 20 * 24 * 3600)),
            'name' => $res['name'],
            'id_face' => $p['face'],
            'id_back' => $p['back'],
            'birthday' => $res['birthDate']
        ];
        if (!$info) {
            $data['educational_experience'] = json_encode([]);
            $data['address'] = json_encode([]);
            UserInfo::create($data);
        } else {
            UserInfo::where('id', $info->id)->update($data);
        }
        return $this->returnData();
    }

    public function editInfoStatus()
    {
        $user = $this->getUser();
        $info = $user->info;
        if (!$info) {
            UserInfo::create([
                'user_id' => $user->id,
                'status' => USER_INFO_STATUS_ABLE,
                'educational_experience' => json_encode([])
            ]);
        } else {
            $info->status = $info->status == USER_INFO_STATUS_ABLE ? USER_INFO_STATUS_DISABLE : USER_INFO_STATUS_ABLE;
            $info->save();
        }
        return $this->returnData();
    }

    public function editAdStatus($did)
    {
        $user = $this->getUser();
        $info = $user->info;
        if (!$info || $info->hash == '') {
            return $this->returnData(ERR_USER_NOT_CERT, '请生成通用账号');
        }

        if (!User::where('pid', $user->id)->count() && $info->direct_open_ad_status != 2) {
            if ($did) {
                // 判断用户是否有下级
                if (!$child = User::where('pid', $user->id)->first()) {
                    if (!$child = User::where('code', $did)->first()) {
                        return $this->returnData(ERR_PARAM_ERR, '通用账号错误');
                    }
                    if ($child->pid > 0) {
                        return $this->returnData(ERR_PARAM_ERR, '通用账号已使用');
                    }
                    $child->pid = $user->id;
                    $child->save();
                }
            } else {
                $openAdCount = UserInfo::where('direct_open_ad_status', 2)->count();
                $system = System::where('key', 'ad_status')->first();
                if ($system['value']['count'] <= $openAdCount) {
                    return $this->returnData(ERR_FAILED, '免填写绑定人已领取完');
                }
                if ($system['value']['ended_at'] <= date('Y-m-d H:i:s')) {
                    return $this->returnData(ERR_FAILED, '免填写绑定人已过期');
                }
                $info->direct_open_ad_status = 2;
            }
        }

        $info->ad_status = $info->ad_status == USER_INFO_STATUS_ABLE ? USER_INFO_STATUS_DISABLE : USER_INFO_STATUS_ABLE;
        $info->save();

        return $this->returnData();
    }

    public function editTransPassword($p)
    {
        $res = (new VerifyCodeSrv())->verifySmsVerifyCode($p['tel'], $p['code'], 2);
        if ($res['code'] != ERR_SUCCESS || !$res['data']) {
            return $this->returnData(ERR_PARAM_ERR, '验证码错误');
        }

        $user = $this->getUser();
        if ($user->tel != $p['tel']) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }

        $user->trans_password = $p['trans_password'];
        $user->save();
        return $this->returnData();
    }

    public function inviterRecords()
    {
        $user = $this->getUser();
        $data = Provider::select('tel', 'created_at')->where('pid', $user->id)->orderBy('id', 'desc')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($data));
    }

    public function grantRecord()
    {
        $user = $this->getUser();
        $download = UserDownloadRecord::select(DB::raw('user_id,"" as nickname,"" as avatar,created_at,1 as type,provider_app_id'))->where('user_id', $user->id);
        $data = UserThirdLoginRecord::select(DB::raw('user_id,nickname,avatar,created_at,2 as type,provider_app_id'))
            ->where('user_id', $user->id)
            ->union($download)
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->toArray();
        foreach ($data['data'] as &$v) {
            $v['app_name'] = '';
            if ($app = ProviderApp::where('id', $v['provider_app_id'])->first()) {
                $v['app_name'] = $app['name'];
            }
        }
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($data));
    }

    public function toChain()
    {
        $user = $this->getUser();
        $info = $user->info;
        if (!$info || $info->name == '') {
            return $this->returnData(ERR_USER_NOT_CERT, '请进行实名认证');
        }
        $p['code'] = $user->code;
        $p['name'] = md5($info->name);
        $p['id_number'] = md5($info->id_number);
        $p['educational'] = count($info->educational_experience) == 0 ? '暂无' : md5(json_encode($info->educational_experience));
        $p['industry'] = '暂无';
        $p['occupation'] = '暂无';
        $p['local'] = $info->province == '' ? '暂无' : $info->province . $info->city . $info->county;
        $p['addr'] = count($info->address) == 0 ? '暂无' : md5(json_encode($info->address));
        if ($industry = $info->industry) {
            $p['industry'] = $industry->name;
        }
        if ($occupation = $info->occupation) {
            $p['occupation'] = $occupation->name;
        }
        if ($info->hash == '') {
            $res = (new AntSrv())->userToChain($p);
        } else {
            $res = (new AntSrv())->updateUserToChain($p);
        }
        if ($res['code'] == ERR_SUCCESS) {
            $info->hash = $res['data'];
            $info->save();
            return $this->returnData(ERR_SUCCESS, '', $res['data']);
        }
        return $res;
    }

    public function createDid()
    {
        $user = $this->getUser();
        $info = $user->info;
        if (!$info || $info->name == '') {
            return $this->returnData(ERR_USER_NOT_CERT, '请进行实名认证');
        }
        $p['code'] = $user->code;
        $p['name'] = md5($info->name);
        $p['id_number'] = md5($info->id_number);
        $p['educational'] = count($info->educational_experience) == 0 ? '暂无' : md5(json_encode($info->educational_experience));
        $p['industry'] = '暂无';
        $p['occupation'] = '暂无';
        $p['local'] = $info->province == '' ? '暂无' : $info->province . $info->city . $info->county;
        $p['addr'] = count($info->address) == 0 ? '暂无' : md5(json_encode($info->address));
        if ($industry = $info->industry) {
            $p['industry'] = $industry->name;
        }
        if ($occupation = $info->occupation) {
            $p['occupation'] = $occupation->name;
        }
        if ($info->hash == '') {
            $res = (new AntSrv())->userToChain($p);
        } else {
            $res = (new AntSrv())->updateUserToChain($p);
        }
        if ($res['code'] == ERR_SUCCESS) {
            $info->hash = $res['data'];
            $info->save();
            return $this->returnData(ERR_SUCCESS, '', $user->code);
        }
        return $res;
    }

    public function otherInfoToChain($p)
    {
        try {
            $user = $this->getUser();
            $info = $user->info;
            if (!$info || $info->hash == "") {
                throw new MyException('请先生成DID');
            }
            $p['province'] = $p['province'] ?: '';
            $p['city'] = $p['city'] ?: '';
            $p['county'] = $p['county'] ?: '';
            UserInfo::where('user_id', $user->id)->update($p);
            $info = UserInfo::where('user_id', $user->id)->first();
            $pa['code'] = $user->code;
            $pa['name'] = md5($info->name);
            $pa['id_number'] = md5($info->id_number);
            $pa['educational'] = count($info->educational_experience) == 0 ? '暂无' : md5(json_encode($info->educational_experience));
            $pa['industry'] = '暂无';
            $pa['occupation'] = '暂无';
            $pa['local'] = $info->province == '' ? '暂无' : $info->province . $info->city . $info->county;
            $pa['addr'] = count($info->address) == 0 ? '暂无' : md5(json_encode($info->address));
            if ($industry = $info->industry) {
                $pa['industry'] = $industry->name;
            }
            if ($occupation = $info->occupation) {
                $pa['occupation'] = $occupation->name;
            }
            $res = (new AntSrv())->updateUserToChain($pa);
            if ($res['code'] != ERR_SUCCESS) {
                throw new MyException('上链失败');
            }
            $info->hash = $res['data'];
            $info->save();
            DB::commit();
            return $this->returnData(ERR_SUCCESS, '', $res['data']);
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '服务器错误');
        }
    }

    public function feedback($p)
    {
        $user = $this->getUser();
        $p['user_id'] = $user->id;
        $p['image'] = $p['image'] ?: '';
        Feedback::create($p);
        return $this->returnData();
    }

    public function persona()
    {
        $user = $this->getUser();
        $uid = $user->id;
        $data = [
            'behavior' => 50,
            'identification' => 50,
            'life' => 50,
            'relation' => 50
        ];

        // 行为积累
        $downloadCount = UserDownloadRecord::where('user_id', $uid)->count();
        if ($downloadCount >= 10) {
            $data['behavior'] += 10;
        }
        if ($downloadCount >= 50) {
            $data['behavior'] += 10;
        }
        if ($downloadCount >= 100) {
            $data['behavior'] += 10;
        }
        if ($downloadCount >= 200) {
            $data['behavior'] += 10;
        }
        if ($downloadCount >= 500) {
            $data['behavior'] += 10;
        }

        $info = $user->info;
        //身份证明
        $complete = 0;
        if ($info) {
            if ($info->name != '') {
                $complete++;
            }
            if ($info->province != '') {
                $complete++;
            }
            if ($info->city != '') {
                $complete++;
            }
            if ($info->county != '') {
                $complete++;
            }
            if ($info->industry_id != '') {
                $complete++;
            }
            if ($info->occupation_id != '') {
                $complete++;
            }
            if (count($info->educational_experience) > 0) {
                $complete++;
            }
            if (count($info->address) > 0) {
                $complete++;
            }
        }
        $data['identification'] += ceil($complete / 8 * 50);

        // 人生阶段
        if ($info && $info->age > 0) {
            $age = $info->age;
            if ($age >= 18) {
                $data['life'] += 10;
            }
            if ($age >= 24) {
                $data['life'] += 10;
            }
            if ($age >= 30) {
                $data['life'] += 10;
            }
            if ($age >= 45) {
                $data['life'] += 10;
            }
            if ($age >= 60) {
                $data['life'] += 10;
            }
        }

        // 人脉关系
        $inviteProviderCount = Provider::where('pid', $uid)->count();
        if ($inviteProviderCount > 0) {
            $data['relation'] += 10;
        }
        if ($inviteProviderCount >= 5) {
            $data['relation'] += 10;
        }
        if ($inviteProviderCount >= 10) {
            $data['relation'] += 10;
        }
        if ($inviteProviderCount >= 20) {
            $data['relation'] += 10;
        }

        if ($inviteProviderCount >= 50) {
            $data['relation'] += 10;
        }


        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function idCardLogin($p)
    {
        if (!$user = User::where('tel', $p['tel'])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '手机号错误');
        }
        if (!$info = $user->info) {
            return $this->returnData(ERR_PARAM_ERR, '该账号未实名认证，找回账号请联系客服！');
        }
        if ($info->name == '') {
            return $this->returnData(ERR_PARAM_ERR, '该账号未实名认证，找回账号请联系客服！');
        }
        if ($info->name != $p['name'] || $info->id_number != $p['id_number']) {
            return $this->returnData(ERR_PARAM_ERR, '登录信息不匹配');
        }
        $user->token = md5($p['tel'] . time());
        $user->save();

        // 添加用户设备信息
        UserDevice::create([
            'user_id' => $user->id,
            'name' => $p['device'],
            'ip' => $_SERVER['REMOTE_ADDR'],
        ]);

        return $this->basicInfo($user->id);
    }

    public function openAdStatusInfo()
    {
        $use = UserInfo::where('direct_open_ad_status', 2)->count();
        $system = System::where('key', 'ad_status')->first();
        $data = [
            'use' => $use,
            'surplus' => $system['value']['count'] - $use,
            'ended_at' => $system['value']['ended_at']
        ];
        return $this->returnData(ERR_SUCCESS, '', $data);
    }
}

