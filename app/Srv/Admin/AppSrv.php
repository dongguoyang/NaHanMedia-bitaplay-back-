<?php


namespace App\Srv\Admin;


use App\Models\Provider;
use App\Models\ProviderApp;
use App\Models\ProviderAppCategory;
use App\Models\ProviderAppGrade;
use App\Models\ProviderAppShop;
use App\Models\ProviderAppVersion;
use App\Models\ProviderAppVersionCategory;
use App\Models\ProviderAppVersionGrade;
use App\Models\ProviderAppVersionShop;
use App\Models\System;
use App\Models\UserDownloadRecord;
use App\Models\UserInviteReward;
use App\Models\UserWallet;
use App\Models\UserWalletRecord;
use App\Srv\MyException;
use App\Srv\Srv;
use Illuminate\Support\Facades\DB;

class AppSrv extends Srv
{
    public function list($p)
    {
        $query = ProviderApp::with('shop', 'l3', 'grade', 'provider');
        if ($p['name']) {
            $query->where('name', 'like', "%{$p['name']}%");
        }
        if ($p['status'] > 0) {
            $query->where('status', $p['status']);
        }
        if ($p['is_recommend'] > -1) {
            $query->where('is_recommend', $p['is_recommend']);
        }
        if ($p['provider_id'] > 0) {
            $query->where('provider_id', $p['provider_id']);
        }
        if ($p['app_id']) {
            $query->where('app_id', 'like', "%{$p['app_id']}%");
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function version($p)
    {
        $query = ProviderAppVersion::with('shop', 'l3', 'grade', 'app');
        if ($p['name']) {
            $query->whereHas('app', function ($query) use ($p) {
                $query->where('name', 'like', "%{$p['name']}%");
            });
        }
        if ($p['status'] > 0) {
            $query->where('status', $p['status']);
        }
        if ($p['app_id']) {
            $query->whereHas('app', function ($query) use ($p) {
                $query->where('app_id', 'like', "%{$p['app_id']}%");
            });
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function editVersionStatus($p)
    {
        try {
            DB::beginTransaction();
            // ????????????
            if (!$version = ProviderAppVersion::where('id', $p['id'])->first()) {
                throw  new MyException('????????????');
            }
            if ($version->status != APP_VERSION_STATUS_PENDING) {
                throw  new MyException('?????????');
            }
            $version->status = $p['status'];
            $version->refuse_reason = $p['refuse_reason'] ?: '';
            $version->save();

            if ($p['status'] == APP_VERSION_STATUS_ABLE) {
                // ????????????
                $provider = Provider::where('id', $version->provider_id)->first();
                if ($provider->pid > 0) {
                    // ???????????????????????????
                    if (ProviderApp::where(['provider_id', $version->provider_id, 'version' => ''])->count() == 0) {
                        $rewardSystem = System::where('key', 'recommend_reward')->first();
                        $reward = $rewardSystem['value']['amount'];
                        // ????????????
                        $wallet = UserWallet::where('user_id', $provider->pid)->lockForUpdate()->first();
                        $wallet->balance += $reward;
                        $wallet->save();
                        // ?????????????????????
                        $number = 'IR' . time() . rand(100000, 999999);
                        $record = UserInviteReward::create([
                            'user_id' => $provider->pid,
                            'provider_id' => $provider->id,
                            'provider_app_id' => $version->provider_app_id,
                            'number' => $number,
                            'amount' => $reward
                        ]);
                        // ???????????????
                        UserWalletRecord::create([
                            'user_id' => $provider->pid,
                            'order_id' => $record->id,
                            'number' => $number,
                            'payment_method' => PAYMENT_METHOD_REWARD,
                            'amount' => $reward,
                            'type' => USER_WALLET_RECORD_INVITE_REWARD
                        ]);
                    }
                }
                // ??????
                $category = ProviderAppVersionCategory::where('provider_app_version_id', $p['id'])->get();
                // ????????????
                $grade = ProviderAppVersionGrade::where('provider_app_version_id', $p['id'])->get();
                // ??????
                if ($version->is_android) {
                    $shop = ProviderAppVersionShop::where('provider_app_version_id', $p['id'])->get();
                }

                // ????????????
                if (!$app = ProviderApp::where('id', $version->provider_app_id)->lockForUpdate()->first()) {
                    throw  new MyException('????????????');
                }
                if ($app->version) {
                    // ????????????
                    ProviderAppCategory::where('provider_app_id', $app->id)->delete();
                    // ??????????????????
                    ProviderAppGrade::where('provider_app_id', $app->id)->delete();
                    // ????????????
                    if ($app->is_android) {
                        ProviderAppShop::where('provider_app_id', $app->id)->delete();
                    }
                }
                // ??????????????????
                $app->version = $version->version;
                $app->desc = $version->desc;
                $app->image = $version->image;
                $app->web = $version->web;
                $app->ios = $version->ios;
                $app->is_ios = $version->is_ios;
                $app->is_android = $version->is_android;
                $app->is_web = $version->is_web;
                $app->package_name = $version->package_name;
                $app->ios_package_name = $version->ios_package_name;
                $app->icon = $version->icon;
                $app->banner = $version->banner;
                $app->save();
                // ????????????
                foreach ($category as $v) {
                    ProviderAppCategory::create([
                        'provider_app_id' => $app->id,
                        'l1' => $v['l1'],
                        'l2' => $v['l2'],
                        'l3' => $v['l3'],
                    ]);
                }
                // ??????????????????
                foreach ($grade as $v) {
                    ProviderAppGrade::create([
                        'provider_app_id' => $app->id,
                        'app_grade_id' => $v['app_grade_id']
                    ]);
                }
                // ????????????
                if ($version->is_android) {
                    foreach ($shop as $v) {
                        ProviderAppShop::create([
                            'provider_app_id' => $app['id'],
                            'android_shop_id' => $v['android_shop_id']
                        ]);
                    }
                }
            }
            DB::commit();
            return $this->returnData();
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '???????????????');
        }
    }

    public function editRecommend($id)
    {
        try {
            DB::beginTransaction();
            if (!$app = ProviderApp::where('id', $id)->lockForUpdate()->first()) {
                throw new MyException('????????????');
            }
            $app->is_recommend = $app->is_recommend == 1 ? 0 : 1;
            $app->save();
            DB::commit();
            return $this->returnData();
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, '???????????????');
        }

    }

    public function editDownloadCount($id, $count)
    {
        try {
            DB::beginTransaction();
            if (!$app = ProviderApp::where('id', $id)->lockForUpdate()->first()) {
                throw new MyException('????????????');
            }
            $app->download_count += $count;
            $app->save();
            DB::commit();
            return $this->returnData();
        } catch (MyException $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_PARAM_ERR, '???????????????');
        }

    }

    public function downloadRecord($p)
    {
        $query = UserDownloadRecord::with('app', 'app.l1', 'app.l2');
        if ($p['user_id'] > 0) {
            $query->where('user_id', $p['user_id']);
        }
        if ($p['name']) {
            $query->whereHas('app', function ($query) use ($p) {
                $query->where('name', 'like', "%{$p['name']}%");
            });
        }
        if ($p['category'] && count($p['category']) > 0 && $p['category'][0] > 0) {
            $query->whereHas('category', function ($query) use ($p) {
                $query->where('l1', $p['category'][0]);
            });
            if (isset($p['category'][1])) {
                $query->whereHas('category', function ($query) use ($p) {
                    $query->where('l2', $p['category'][1]);
                });
            }
        }
        if ($p['time'] && count($p['time']) > 0) {
            $start = "{$p['time'][0]} 00:00:00";
            $query->where('created_at', '>=', $start);
            if (isset($p['time'][1])) {
                $end = "{$p['time'][1]} 23:59:59";
                $query->where('created_at', '<=', $end);
            }
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }
}
