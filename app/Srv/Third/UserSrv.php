<?php


namespace App\Srv\Third;

use App\Models\Provider;
use App\Models\ProviderApp;
use App\Models\ProviderAppUsers;
use App\Models\ProviderInfo;
use App\Models\User;
use App\Models\UserDownloadCategory;
use App\Models\UserDownloadRecord;
use App\Models\UserThirdLoginRecord;
use App\Models\UserWallet;
use App\Models\UserWalletRecord;
use App\Srv\MyException;
use App\Srv\Provider\RemindSrv;
use App\Srv\Srv;
use App\Srv\Utils\SmsSrv;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserSrv extends Srv
{
    public function info($p)
    {
        try {
            Log::info("查询用户信息：", $p);
            if (!$app = ProviderApp::where('app_key', $p['app_key'])->lockForUpdate()->first()) {
                return $this->returnData(ERR_PARAM_ERR, 'APP KEY错误');
            }
            $provider = $app->provider;
            if ($provider->status != PROVIDER_STATUS_ABLE) {
                throw new MyException('已禁用');
            }
            // 解密
            $data = $this->decrypt($p['data'], $app->app_secret);
            if (!$data) {
                throw new MyException('解密失败');
            }
            $data = json_decode($data, true);
            if (!isset($data['code']) || !isset($data['app_id']) || $data['app_id'] != $app->app_id) {
                throw new MyException('加密信息错误');
            }
            // 获取缓存数据
            if (!$cacheData = Cache::pull($data['code'])) {
                throw new MyException('CODE已过期');
            }
            $cacheData = json_decode($cacheData, true);
            $appId = $cacheData['aid'];
            $userId = $cacheData['uid'];
            if ($app->id != $appId) {
                throw new MyException('参数错误');
            }

            // 是否需要发送提示信息
            $remind = false;
            $remindAmount = $app->remind_third_login;
            $thirdLoginAmount = $app->third_login_amount;
            if ($remindAmount > 0 && $thirdLoginAmount >= $remindAmount && $thirdLoginAmount - 1 < $remindAmount) {
                $remind = true;
            }

            // 更新三方登录信息
            $app->third_login_amount -= 1;
            $app->save();
            $user = User::where('id', $userId)->first();
            // 写登录记录
            UserThirdLoginRecord::create([
                'user_id' => $userId,
                'provider_app_id' => $app->id,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'nickname' => $user->nickname,
                'avatar' => $user->avatar
            ]);

            $returnData = [
                'tel' => '',
                'nickname' => '',
                'avatar' => '',
                // 'email' => '',
                // 'desc' => '',
                // 'province' => '',
                // 'city' => '',
                // 'county' => '',
                // 'industry' => '',
                // 'occupation' => '',
                // 'educational_experience' => [],
                // 'address' => [],
                'open_id' => '',
            ];
            $returnData['tel'] = $user->tel;
            $returnData['nickname'] = $user->nickname;
            $returnData['avatar'] = $user->avatar;
            // $returnData['email'] = $user->email;
            // $returnData['desc'] = $user->desc;
            $open = ProviderAppUsers::where(['user_id' => $userId, 'provider_app_id' => $app->id])->first();
            $returnData['open_id'] = $open->open_id;

            $info = $user->info;
            // if ($info && $info->status = USER_INFO_STATUS_ABLE) {
            //     $returnData['province'] = $info->province;
            //     $returnData['city'] = $info->city;
            //     $returnData['county'] = $info->county;
            //     $returnData['educational_experience'] = $info->educational_experience;
            //     $returnData['address'] = $info->address;
            //     if ($occupation = $info->occupation) {
            //         $returnData['occupation'] = $occupation->name;
            //     }
            //     if ($industry = $info->industry) {
            //         $returnData['industry'] = $industry->name;
            //     }
            // }

            if ($info) {
                // 推广奖励
                $this->downloadReward($info, $app);
            }
            $returnData = $this->encrypt(json_encode($returnData), $app->app_secret);
            DB::commit();

            // 发送短信
            if ($remind) {
                (new RemindSrv())->sendRemindProvider($app->provider_id, '三方登录功能', "{$remindAmount}次");
            }

            return $this->returnData(ERR_SUCCESS, '', $returnData);
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '服务器错误');
        }
    }

    public function downloadReward($info, $app)
    {
        $download = UserDownloadRecord::where(['user_id' => $info->user_id, 'provider_app_id' => $app->id])->first();
        if (!$download || $download->status == 1) {
            if (!$download) {
                $number = 'DR' . time() . rand(100000, 999999);
            } else {
                $number = $download->number;
            }
            $rewardAmount = 0;
            $userRewardAmount = 0;
            if ($app->is_download_reward == 1 && $app->reward_amount > 0 && $app->download_reward_status == 1) {
                $rewardAmount = $app->reward_amount > $app->download_reward ? $app->download_reward : $app->reward_amount;
                if ($info->ad_status = USER_INFO_STATUS_ABLE) {
                    // 计算用户信息完成度
                    $completeCount = 1;
                    if ($info->province != '') {
                        $completeCount++;
                    }
                    if ($info->city != '') {
                        $completeCount++;
                    }
                    if ($info->county != '') {
                        $completeCount++;
                    }
                    if ($info->industry_id != '') {
                        $completeCount++;
                    }
                    if ($info->occupation_id != '') {
                        $completeCount++;
                    }
                    if (count($info->educational_experience) > 0) {
                        $completeCount++;
                    }
                    if (count($info->address) > 0) {
                        $completeCount++;
                    }
                    $userRewardAmount = floor($rewardAmount * ($completeCount / 8));
                    if ($userRewardAmount > $rewardAmount) {
                        $userRewardAmount = $rewardAmount;
                    }
                }
            }
            $remind = false;
            $surplusRewardAmount = $app->reward_amount;
            $remindAmount = $app->remind_reward;
            if ($remindAmount > 0 && $surplusRewardAmount >= $remindAmount && $surplusRewardAmount - $rewardAmount < $remindAmount) {
                $remind = true;
            }
            // 更新佣金
            $app->reward_amount -= $rewardAmount;
            $app->save();

            if (!$download) {
                // 写下载记录
                $download = UserDownloadRecord::create([
                    'user_id' => $info->user_id,
                    'provider_app_id' => $app->id,
                    'number' => $number,
                    'reward_amount' => $rewardAmount,
                    'user_reward_amount' => $userRewardAmount,
                    'status' => 2,
                ]);
                // 写用户画像
                $category = $app->category;
                $insert = [];
                foreach ($category as $k => $v) {
                    $insert[$k] = [
                        'user_id' => $info->user_id,
                        'l1' => $v->l1,
                        'l2' => $v->l2,
                        'l3' => $v->l3,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                }
                UserDownloadCategory::insert($insert);
            } else {
                $download->status = 2;
                $download->save();
            }

            // 写用户钱包记录和更新用户钱包
            if ($userRewardAmount > 0) {
                // 更新用户钱包
                $wallet = UserWallet::where('user_id', $info->user_id)->lockForUpdate()->first();
                $wallet->balance += $userRewardAmount;
                $wallet->save();
                // 写用户钱包记录
                UserWalletRecord::create([
                    'user_id' => $info->user_id,
                    'order_id' => $download->id,
                    'number' => $number,
                    'payment_method' => PAYMENT_METHOD_REWARD,
                    'amount' => $userRewardAmount,
                    'type' => USER_WALLET_RECORD_DOWNLOAD_REWARD
                ]);
            }


            // 发送短信
            if ($remind) {
                (new RemindSrv())->sendRemindProvider($app->provider_id, '推广功能', "{$remindAmount}元");
            }

        }
    }

    public function webLogin($p)
    {
        // $url = isset($_SERVER['HTTP_ORIGIN']) ?: '';
        // if ($url == '') {
        //     return $this->returnData(ERR_FAILED, '非法操作');
        // }

        if (!$app = ProviderApp::where('app_id', $p['app_id'])->first()) {
            return $this->returnData(ERR_PARAM_ERR, 'APP ID错误');
        }
        if ($app->third_login_amount == 0) {
            return $this->returnData(ERR_PARAM_ERR, '三方登录次数已告罄，请充值后使用');
        }
        //
        // if (!Str::startsWith($app->web, $url)) {
        //     return $this->returnData(ERR_PARAM_ERR, '非法操作');
        // }

        $qrcodeContent = Crypt::encrypt([
            'grant_code' => $p['grant_code'],
            'app_id' => $p['app_id'],
            'time' => time(),
            'callback_url' => $p['callback_url']
        ]);
        $code = md5($qrcodeContent);
        Cache::put("twl:{$code}", $qrcodeContent, 300);
        return $this->returnData(ERR_SUCCESS, '', 'BitaPlay:' . $code);
    }
}




