<?php


namespace App\Srv\Provider;

use App\Models\ProviderApp;
use App\Models\ProviderAppChainRecord;
use App\Models\ProviderAppRechargeFuelRecord;
use App\Models\ProviderAppRechargeRewardRecord;
use App\Models\ProviderAppRechargeThirdLoginRecord;
use App\Models\ProviderAppRecommendCategory;
use App\Models\ProviderAppVersion;
use App\Models\ProviderAppVersionCategory;
use App\Models\ProviderAppVersionGrade;
use App\Models\ProviderAppVersionShop;
use App\Models\ProviderWallet;
use App\Models\System;
use App\Models\UserDownloadRecord;
use App\Models\UserThirdLoginRecord;
use App\Srv\MyException;
use App\Srv\Srv;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppSrv extends Srv
{
    public function list($p)
    {
        $provider = $this->getProvider();
        $query = ProviderApp::with(['versions' => function ($query) {
            $query->where('status', APP_VERSION_STATUS_PENDING);
        }, 'shop', 'l3', 'grade'])
            ->withCount('login', 'download', 'chain')
            ->where('provider_id', $provider->id);
        if ($p['name']) {
            $query->where('name', 'like', "%{$p['name']}%");
        }
        if ($p['status'] > 0) {
            $query->where('status', $p['status']);
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function detail($id)
    {
        $provider = $this->getProvider();
        if (!$app = ProviderApp::where('id', $id)->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        if ($app->provider_id != $provider->id) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        return $this->returnData(ERR_SUCCESS, '', $app);
    }

    public function create($p)
    {
        $provider = $this->getProvider();
        if (ProviderApp::where('name', $p['name'])->count() > 0) {
            return $this->returnData(ERR_PARAM_ERR, '应用名称已占用');
        }
        $p['provider_id'] = $provider->id;
        $p['image'] = [];
        $p['app_id'] = md5($provider->id . $p['name']);
        $p['app_key'] = md5($p['app_id'] . time());
        $p['app_secret'] = substr(md5($p['name'] . time()), 0, 16);
        $p['recommend_city'] = ['0'];
        $p['recommend_industry'] = ['0'];
        $p['recommend_sex'] = '0';
        $p['recommend_age'] = ['0'];
        $p['recommend_preference'] = ['0'];
        $p['recommend_style'] = ['0'];
        $p['recommend_educational'] = ['0'];
        $p['recommend_device'] = ['0'];
        $p['recommend_system'] = 0;
        $p['recommend_real'] = '0';
        $p['recommend_week_download'] = '0';
        $p['recommend_month_download'] = '0';
        ProviderApp::create($p);
        return $this->returnData();
    }

    public function versionList($status, $appId)
    {
        $query = ProviderAppVersion::with('shop', 'l3', 'grade');
        if ($status > 0) {
            $query->where('status', $status);
        }
        if ($appId > 0) {
            $query->where('provider_app_id', $appId);
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function saveVersion($p)
    {
        if (!$app = ProviderApp::where('id', $p['provider_app_id'])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        $provider = $this->getProvider();
        if ($app->provider_id != $provider->id) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }

        if (ProviderAppVersion::where(['provider_app_id' => $p['provider_app_id'], 'status' => APP_VERSION_STATUS_PENDING])->count() > 0) {
            return $this->returnData(ERR_PARAM_ERR, '有正在审核的版本');
        }
        if ($successVersion = ProviderAppVersion::where(['provider_app_id' => $p['provider_app_id'], 'status' => APP_VERSION_STATUS_ABLE])->orderBy('id', 'desc')->first()) {
            if ($successVersion->version >= $p['version']) {
                return $this->returnData(ERR_PARAM_ERR, '版本号必须大于已通过审核的版本号');
            }
        }
        $p['provider_id'] = $provider->id;
        $p['status'] = APP_VERSION_STATUS_PENDING;
        $shop = $p['shop'];
        $category = $p['category'];
        $grade = $p['grade'];
        unset($p['shop']);
        unset($p['category']);
        unset($p['grade']);
        $p['ios'] = $p['ios'] ?: '';
        $p['web'] = $p['web'] ?: '';
        $p['package_name'] = isset($p['package_name']) ? $p['package_name'] : '';
        $p['ios_package_name'] = isset($p['ios_package_name']) ? $p['ios_package_name'] : '';
        if ($p['is_ios'] == 1) {
            $packageApp = ProviderAppVersion::where(['is_ios' => 1, 'ios_package_name' => $p['ios_package_name']])->first();
            if ($packageApp && $packageApp->provider_app_id != $p['provider_app_id']) {
                return $this->returnData(ERR_PARAM_ERR, 'IOS包名重复');
            }
        }
        if ($p['is_android'] == 1) {
            $packageApp = ProviderAppVersion::where(['is_android' => 1, 'package_name' => $p['package_name']])->first();
            if ($packageApp && $packageApp->provider_app_id != $p['provider_app_id']) {
                return $this->returnData(ERR_PARAM_ERR, 'Android包名重复');
            }
        }
        try {
            DB::beginTransaction();
            if ($p['id'] > 0) {
                if (!$version = ProviderAppVersion::where('id', $p['id'])->first()) {
                    return $this->returnData(ERR_PARAM_ERR, '非法操作');
                }
                ProviderAppVersion::where('id', $p['id'])->update($p);
                // 删除分类
                ProviderAppVersionCategory::where('provider_app_version_id', $p['id'])->delete();
                // 删除适用人群
                ProviderAppVersionGrade::where('provider_app_version_id', $p['id'])->delete();
                // 删除商店
                ProviderAppVersionShop::where('provider_app_version_id', $p['id'])->delete();
            } else {
                $version = ProviderAppVersion::create($p);
                $p['id'] = $version->id;
            }

            // 添加分类
            foreach ($category as $v) {
                ProviderAppVersionCategory::create([
                    'provider_app_version_id' => $p['id'],
                    'l1' => $v[0],
                    'l2' => $v[1],
                    'l3' => $v[2],
                ]);
            }
            // 添加适用人群
            foreach ($grade as $v) {
                ProviderAppVersionGrade::create([
                    'provider_app_version_id' => $p['id'],
                    'app_grade_id' => $v
                ]);
            }
            // 添加商店
            foreach ($shop as $v) {
                ProviderAppVersionShop::create([
                    'provider_app_version_id' => $p['id'],
                    'android_shop_id' => $v
                ]);
            }
            DB::commit();
            return $this->returnData();
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '保存失败');
        }
    }

    public function versionDetail($id)
    {
        $provider = $this->getProvider();
        if (!$version = ProviderAppVersion::where('id', $id)->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        if ($version->provider_id != $provider->id) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        $version = $version->toArray();
        $version['shop'] = [];
        $version['category'] = [];
        $version['grade'] = [];
        // 店铺ID
        if ($version['is_android'] == 1) {
            $shop = ProviderAppVersionShop::where('provider_app_version_id', $id)->get();
            foreach ($shop as $v) {
                $version['shop'][] = $v['id'];
            }
        }
        //分类ID
        $category = ProviderAppVersionCategory::where('provider_app_version_id', $id)->get();
        foreach ($category as $v) {
            $version['category'][] = [$v['l1'], $v['l2'], $v['l3']];
        }
        //使用人群ID
        $grade = ProviderAppVersionGrade::where('provider_app_version_id', $id)->get();
        foreach ($grade as $v) {
            $version['grade'][] = $v['id'];
        }
        return $this->returnData(ERR_SUCCESS, '', $version);
    }

    public function editStatus($id)
    {
        try {
            DB::beginTransaction();
            $provider = $this->getProvider();
            if (!$app = ProviderApp::where('id', $id)->lockForUpdate()->first()) {
                throw  new MyException('非法操作');
            }
            if ($app->provider_id != $provider->id) {
                throw  new MyException('非法操作');
            }
            if ($app->is_third_login != 1) {
                throw  new MyException('请开通三方登录功能');
            }
            $app->status = $app->status == APP_STATUS_ABLE ? APP_STATUS_DISABLE : APP_STATUS_ABLE;
            $app->save();
            DB::commit();
            return $this->returnData();
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '服务器错误');
        }
    }

    public function appDetail($id)
    {
        $provider = $this->getProvider();
        $app = ProviderApp::where(['id' => $id, 'provider_id' => $provider->id])
            ->withCount('login', 'download', 'chain')
            ->with('shop', 'l3', 'grade')
            ->first();
        if (!$app) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        return $this->returnData(ERR_SUCCESS, '', $app);
    }

    public function rechargeDownloadReward($p)
    {
        $provider = $this->getProvider();
        if (!$provider->trans_password || $p['trans_password'] != $provider->trans_password) {
            return $this->returnData(ERR_PARAM_ERR, '支付密码错误');
        }
        $amount = $p['amount'] * 100;
        if ($amount < 500000) {
            return $this->returnData(ERR_PARAM_ERR, '充值金额必须大于￥5000');
        }
        try {
            DB::beginTransaction();
            $app = ProviderApp::where(['id' => $p['id'], 'provider_id' => $provider->id])->lockForUpdate()->first();
            if (!$app) {
                throw new MyException('非法操作');
            }
            $wallet = ProviderWallet::where('provider_id', $provider->id)->lockForUpdate()->first();
            if ($wallet->balance < $amount) {
                throw new MyException('余额不足');
            }
            // 更新APP信息
            $app->reward_amount += $amount;
            $app->is_download_reward = 1;
            $app->save();
            // 更新钱包信息
            $wallet->balance -= $amount;
            $wallet->save();
            // 写充值信息
            ProviderAppRechargeRewardRecord::create([
                'provider_id' => $provider->id,
                'provider_app_id' => $p['id'],
                'number' => 'RI' . time() . rand(100000, 999999),
                'amount' => $amount
            ]);
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

    public function editDownloadReward($p)
    {
        $provider = $this->getProvider();
        if (!$provider->trans_password || $p['trans_password'] != $provider->trans_password) {
            return $this->returnData(ERR_PARAM_ERR, '支付密码错误');
        }
        $amount = $p['amount'] * 100;
        if ($amount <= 0) {
            return $this->returnData(ERR_PARAM_ERR, '下载佣金必须大于￥0');
        }
        $system = System::where('key', 'lowest_download_reward')->first();
        if ($system['value']['amount'] > $amount) {
            $lowestAmount = $system['value']['amount'] / 100;
            return $this->returnData(ERR_PARAM_ERR, "最低下载佣金为￥{$lowestAmount}");
        }
        try {
            DB::beginTransaction();
            $app = ProviderApp::where(['id' => $p['id'], 'provider_id' => $provider->id])->lockForUpdate()->first();
            if (!$app) {
                throw new MyException('非法操作');
            }
            // 更新APP信息
            $app->download_reward = $amount;
            $app->save();
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

    public function rechargeThirdLogin($p)
    {
        $provider = $this->getProvider();
        if (!$provider->trans_password || $p['trans_password'] != $provider->trans_password) {
            return $this->returnData(ERR_PARAM_ERR, '支付密码错误');
        }
        $amount = $p['amount'] * 100;
        if ($amount < 100000) {
            return $this->returnData(ERR_PARAM_ERR, '充值金额必须大于￥1000');
        }
        $system = System::where('key', 'third_login_price')->first();
        $price = $system['value']['price'];
        // 计算次数
        $loginAmount = floor($amount / $price);
        if ($loginAmount == 0) {
            return $this->returnData(ERR_PARAM_ERR, '充值金额必须大于￥' . $price / 100);
        }
        $amount = $price * $loginAmount;

        try {
            DB::beginTransaction();
            $app = ProviderApp::where(['id' => $p['id'], 'provider_id' => $provider->id])->lockForUpdate()->first();
            if (!$app) {
                throw new MyException('非法操作');
            }

            $wallet = ProviderWallet::where('provider_id', $provider->id)->lockForUpdate()->first();
            if ($wallet->balance < $amount) {
                throw new MyException('余额不足');
            }
            // 更新APP信息
            $app->third_login_amount += $loginAmount;
            $app->is_third_login = 1;
            $app->save();
            // 更新钱包信息
            $wallet->balance -= $amount;
            $wallet->save();
            // 写充值信息
            ProviderAppRechargeThirdLoginRecord::create([
                'provider_id' => $provider->id,
                'provider_app_id' => $p['id'],
                'number' => 'RT' . time() . rand(100000, 999999),
                'amount' => $amount,
                'third_login_amount' => $loginAmount,
                'price' => $price
            ]);
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

    public function rechargeFuel($p)
    {
        $provider = $this->getProvider();
        if (!$provider->trans_password || $p['trans_password'] != $provider->trans_password) {
            return $this->returnData(ERR_PARAM_ERR, '支付密码错误');
        }
        $amount = $p['amount'] * 100;
        if ($amount <= 0) {
            return $this->returnData(ERR_PARAM_ERR, '充值金额必须大于￥0');
        }
        $system = System::where('key', 'fuel_price')->first();
        $price = $system['value']['price'];
        // 计算次数
        $fuelAmount = floor($amount / $price);
        if ($fuelAmount == 0) {
            return $this->returnData(ERR_PARAM_ERR, '充值金额必须大于￥' . $price / 100);
        }
        $amount = $price * $fuelAmount;

        try {
            DB::beginTransaction();
            $app = ProviderApp::where(['id' => $p['id'], 'provider_id' => $provider->id])->lockForUpdate()->first();
            if (!$app) {
                throw new MyException('非法操作');
            }

            $wallet = ProviderWallet::where('provider_id', $provider->id)->lockForUpdate()->first();
            if ($wallet->balance < $amount) {
                throw new MyException('余额不足');
            }
            // 更新APP信息
            $fuelAmount *= 10000;
            $app->fuel_amount += $fuelAmount;
            $app->is_to_chain = 1;
            $app->save();
            // 更新钱包信息
            $wallet->balance -= $amount;
            $wallet->save();
            // 写充值信息
            ProviderAppRechargeFuelRecord::create([
                'provider_id' => $provider->id,
                'provider_app_id' => $p['id'],
                'number' => 'RF' . time() . rand(100000, 999999),
                'amount' => $amount,
                'fuel_amount' => $fuelAmount,
                'price' => $price
            ]);
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

    public function thirdLoginStatistics($id)
    {
        $provider = $this->getProvider();
        if (!$app = ProviderApp::where(['id' => $id, 'provider_id' => $provider->id])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }

        $data['recharge'] = ProviderAppRechargeThirdLoginRecord::select(DB::raw('sum(amount) as amount,sum(third_login_amount) as third_login_amount'))
            ->where('provider_app_id', $id)
            ->groupBy('provider_app_id')
            ->first();
        $data['consume'] = UserThirdLoginRecord::where('provider_app_id', $id)->count();
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function rechargeThirdLoginRecord($id)
    {
        $provider = $this->getProvider();
        if (!$app = ProviderApp::where(['id' => $id, 'provider_id' => $provider->id])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }

        $list = ProviderAppRechargeThirdLoginRecord::where('provider_app_id', $id)
            ->orderBy('id', 'desc')
            ->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function consumeThirdLoginRecord($id)
    {
        $provider = $this->getProvider();
        if (!$app = ProviderApp::where(['id' => $id, 'provider_id' => $provider->id])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }

        $list = UserThirdLoginRecord::where(['provider_app_id' => $id])
            ->with('appUser')
            ->orderBy('id', 'desc')
            ->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }


    public function fuelStatistics($id)
    {
        $provider = $this->getProvider();
        if (!$app = ProviderApp::where(['id' => $id, 'provider_id' => $provider->id])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }

        $data['recharge'] = ProviderAppRechargeFuelRecord::select(DB::raw('sum(amount) as amount,sum(fuel_amount) as fuel_amount'))
            ->where('provider_app_id', $id)
            ->groupBy('provider_app_id')
            ->first();
        $data['consume'] = ProviderAppChainRecord::where('provider_app_id', $id)->sum('fuel');
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function rechargeFuelRecord($id)
    {
        $provider = $this->getProvider();
        if (!$app = ProviderApp::where(['id' => $id, 'provider_id' => $provider->id])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }

        $list = ProviderAppRechargeFuelRecord::where('provider_app_id', $id)
            ->orderBy('id', 'desc')
            ->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function consumeFuelRecord($id)
    {
        $provider = $this->getProvider();
        if (!$app = ProviderApp::where(['id' => $id, 'provider_id' => $provider->id])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }

        $list = ProviderAppChainRecord::where(['provider_app_id' => $id])
            ->orderBy('id', 'desc')
            ->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function downloadStatistics($id)
    {
        $provider = $this->getProvider();
        if (!$app = ProviderApp::where(['id' => $id, 'provider_id' => $provider->id])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }

        $data['recharge'] = ProviderAppRechargeRewardRecord::select(DB::raw('sum(amount) as amount'))
            ->where('provider_app_id', $id)
            ->groupBy('provider_app_id')
            ->first();
        $data['consume'] = UserDownloadRecord::where('provider_app_id', $id)->sum('reward_amount');
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function rechargeDownloadRecord($id)
    {
        $provider = $this->getProvider();
        if (!$app = ProviderApp::where(['id' => $id, 'provider_id' => $provider->id])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }

        $list = ProviderAppRechargeRewardRecord::where('provider_app_id', $id)
            ->orderBy('id', 'desc')
            ->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function consumeDownloadRecord($id)
    {
        $provider = $this->getProvider();
        if (!$app = ProviderApp::where(['id' => $id, 'provider_id' => $provider->id])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }

        $list = UserDownloadRecord::where(['provider_app_id' => $id])
            ->orderBy('id', 'desc')
            ->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function recommend($p)
    {
        $provider = $this->getProvider();
        if (!$app = ProviderApp::where(['id' => $p['id'], 'provider_id' => $provider->id])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        try {
            DB::beginTransaction();
            $app->recommend_city = $p['recommend_city'];
            $app->recommend_industry = $p['recommend_industry'];
            $app->recommend_sex = $p['recommend_sex'];
            $app->recommend_age = $p['recommend_age'];
            $app->recommend_preference = $p['recommend_preference'];
            $app->recommend_style = $p['recommend_style'];
            $app->recommend_educational = $p['recommend_educational'];
            $app->recommend_device = $p['recommend_device'];
            $app->recommend_system = $p['recommend_system'];
            $app->recommend_real = $p['recommend_real'];
            $app->recommend_month_download = $p['recommend_month_download'];
            $app->recommend_week_download = $p['recommend_week_download'];
            $app->recommend_category_cycle = $p['recommend_category_cycle'];
            $app->save();
            // 删除推荐分类
            ProviderAppRecommendCategory::where('provider_app_id', $p['id'])->delete();
            // 创建推荐分类
            if ($p['recommend_preference'] != 0) {
                foreach ($p['recommend_preference'] as $v) {
                    ProviderAppRecommendCategory::create([
                        'provider_app_id' => $p['id'],
                        'l1' => $v[0],
                        'l2' => $v[1],
                        'l3' => $v[2],
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '服务器错误');
        }
        return $this->returnData();
    }

    public function remind($p)
    {
        $provider = $this->getProvider();
        if (!$app = ProviderApp::where(['id' => $p['id'], 'provider_id' => $provider->id])->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        $app->remind_third_login = $p['remind_third_login'];
        $app->remind_reward = $p['remind_reward'] * 100;
        $app->remind_fuel = $p['remind_fuel'];
        $app->save();
        return $this->returnData();
    }

    public function editDownloadRewardStatus($id)
    {
        try {
            DB::beginTransaction();
            $provider = $this->getProvider();
            if (!$app = ProviderApp::where('id', $id)->lockForUpdate()->first()) {
                throw  new MyException('非法操作');
            }
            if ($app->provider_id != $provider->id) {
                throw  new MyException('非法操作');
            }
            $app->download_reward_status = $app->download_reward_status == 0 ? 1 : 0;
            $app->save();
            DB::commit();
            return $this->returnData();
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '服务器错误');
        }
    }
}

