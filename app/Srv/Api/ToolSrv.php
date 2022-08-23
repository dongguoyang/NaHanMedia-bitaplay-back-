<?php


namespace App\Srv\Api;


use App\Models\AppCategory;
use App\Models\Area;
use App\Models\Avatar;
use App\Models\HotWord;
use App\Models\Industry;
use App\Models\KuaishouAds;
use App\Models\Occupation;
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
use App\Srv\Srv;
use Facade\Ignition\SolutionProviders\DefaultDbNameSolutionProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToolSrv extends Srv
{
    public function industry()
    {
        $data = Industry::orderBy('id', 'desc')->get();
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function occupation($industryId)
    {
        $query = Occupation::orderBy('id', 'desc');
        if ($industryId > 0) {
            $query->where('industry_id', $industryId);
        }
        $data = $query->get();
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function area()
    {
        $data = Area::with('child', 'child.child')->where('pid', 0)->get();
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function appCategory($oaid = "")
    {
        $query = AppCategory::with('child', 'child.child')
            ->where('pid', 0);
        if (env('HIDDEN_GAME_CATEGORY')) {
            $query->where('id', '!=', 1);
        }
        $data['tree'] = $query->get();
        $data['list'] = [];
        $i = 0;
        foreach ($data['tree'] as $v) {
            foreach ($v['child'] as $l2v) {
                $data['list'][$i]['id'] = $l2v['id'];
                $data['list'][$i]['name'] = $l2v['name'];
                $i++;
            }
        }
        if ($oaid != "") {
            $this->callKuaishouAds($oaid, 1);
        }
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function system()
    {
        $data = [];
        $systems = System::whereIn('key', ['fee'])->get();
        foreach ($systems as $v) {
            $data[$v['key']] = $v['value'];
        }
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function searchSystem($key)
    {
        $system = System::where('key', $key)->first();
        return $this->returnData(ERR_SUCCESS, '', $system['value']);
    }

    public function agreement()
    {
        $user = $this->searchSystem('user_agreement');
        $privacy = $this->searchSystem('privacy_agreement');
        $provider = $this->searchSystem('provider_agreement');
        $data['user_agreement'] = $user['data']['content'];
        $data['privacy_agreement'] = $privacy['data']['content'];
        $data['provider_agreement'] = $provider['data']['content'];
        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function hotWord()
    {
        $list = HotWord::orderBy('id', 'desc')->get();
        return $this->returnData(ERR_SUCCESS, '', $list);
    }

    public function avatar()
    {
        $list = Avatar::orderBy('id', 'asc')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function grap()
    {
        $path = storage_path('app/result_dis.csv');
        $fp = fopen($path, 'rb');
        $data = [];
        while (!feof($fp)) {
            $data[] = fgetcsv($fp);
        }
        unset($data[0]);
        try {
            // 查询服务商
            $p = Provider::where('tel', '13111111112')->first();
            DB::beginTransaction();
            for ($i = 6601; $i <= 6769; $i++) {
                $v = $data[$i];
                if (count($v) < 7) {
                    continue;
                }

                $category = AppCategory::with('parent', 'parent.parent')
                    ->where('name', $v[0])
                    ->first();
                if (!$category) {
                    continue;
                }
                if (ProviderApp::where('name', $v['1'])->count() > 0) {
                    continue;
                }
                $name = $v[1];
                $package = $v[2];
                $icon = $v[3];
                $version = $v[4];
                $desc = trim(str_replace("\n", "", str_replace("\r", "", $v[5])), '"');
                $image = explode(';', $v[6]);
                // 插入APP
                $app = ProviderApp::create([
                    'provider_id' => $p->id,
                    'app_id' => md5($name . time() . $package),
                    'app_key' => md5($name . time() . $package . time()),
                    'app_secret' => substr(md5($name), 0, 16),
                    'version' => $version,
                    'name' => $name,
                    'package_name' => $package,
                    'icon' => $icon,
                    'desc' => $desc,
                    'image' => $image,
                    'is_android' => 1,
                    'is_download_reward' => 0,
                    'is_third_login' => 1,
                    'status' => 2,
                    'third_login_amount' => 0,
                    'recommend_city' => [0],
                    'recommend_industry' => [0],
                    'recommend_age' => [0],
                    'recommend_preference' => 0,
                    'recommend_style' => [0],
                    'recommend_educational' => [0],
                    'recommend_device' => [0],
                    'recommend_system' => 0,
                    'recommend_real' => 0,
                    'recommend_week_download' => 0,
                    'recommend_month_download' => 0,
                    'download_reward_status' => 0,
                    'banner' => '',
                    'recommend_category_cycle' => 0,
                    'download_count' => rand(1000, 100000)
                ]);
                // 插入分类
                $l1 = 0;
                $l2 = 0;
                $l3 = 0;
                if ($category->parent && !$category->parent->parent) {
                    $l2 = $category->id;
                    $l1 = $category->parent->id;
                }
                if (!$category->parent) {
                    $l1 = $category->id;
                }
                if ($category->parent && $category->parent->parent) {
                    $l1 = $category->parent->parent->id;
                    $l2 = $category->parent->id;
                    $l3 = $category->id;
                }
                ProviderAppCategory::create([
                    'provider_app_id' => $app->id,
                    'l1' => $l1,
                    'l2' => $l2,
                    'l3' => $l3
                ]);
                // 插入分级
                ProviderAppGrade::create([
                    'provider_app_id' => $app->id,
                    'app_grade_id' => 3,
                ]);
                // 写应用商店
                $now = date('Y-m-d H:i:s');
                ProviderAppShop::insert([
                    ['provider_app_id' => $app->id, 'android_shop_id' => 1, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_id' => $app->id, 'android_shop_id' => 3, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_id' => $app->id, 'android_shop_id' => 6, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_id' => $app->id, 'android_shop_id' => 19, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_id' => $app->id, 'android_shop_id' => 13, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_id' => $app->id, 'android_shop_id' => 4, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_id' => $app->id, 'android_shop_id' => 5, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_id' => $app->id, 'android_shop_id' => 20, 'created_at' => $now, 'updated_at' => $now],
                ]);

                // 写版本
                $app = ProviderAppVersion::create([
                    'provider_id' => $p->id,
                    'provider_app_id' => $app->id,
                    'version' => $version,
                    'name' => $name,
                    'package_name' => $package,
                    'icon' => $icon,
                    'desc' => $desc,
                    'image' => $image,
                    'is_android' => 1,
                    'is_download_reward' => 0,
                    'is_third_login' => 1,
                    'status' => 2,
                    'banner' => '',
                ]);
                // 插入分类
                ProviderAppVersionCategory::create([
                    'provider_app_version_id' => $app->id,
                    'l1' => $l1,
                    'l2' => $l2,
                    'l3' => $l3
                ]);
                // 插入分级
                ProviderAppVersionGrade::create([
                    'provider_app_version_id' => $app->id,
                    'app_grade_id' => 3,
                ]);
                // 写应用商店
                ProviderAppVersionShop::insert([
                    ['provider_app_version_id' => $app->id, 'android_shop_id' => 1, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_version_id' => $app->id, 'android_shop_id' => 3, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_version_id' => $app->id, 'android_shop_id' => 6, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_version_id' => $app->id, 'android_shop_id' => 19, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_version_id' => $app->id, 'android_shop_id' => 13, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_version_id' => $app->id, 'android_shop_id' => 4, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_version_id' => $app->id, 'android_shop_id' => 5, 'created_at' => $now, 'updated_at' => $now],
                    ['provider_app_version_id' => $app->id, 'android_shop_id' => 20, 'created_at' => $now, 'updated_at' => $now],
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getLine(), $e->getMessage());
        }
    }

    public function updateDownload()
    {
        $path = storage_path('app/app_list_download.csv');
        $fp = fopen($path, 'rb');
        $data = [];
        while (!feof($fp)) {
            $data[] = fgetcsv($fp);
        }
        unset($data['0']);
        try {
            $total = count($data);
            for ($i = 1; $i <= $total - 1; $i++) {
                $v = $data[$i];
                if (!$app = ProviderApp::where('name', $v['0'])->where('id', '>', 4995)->first()) {
                    continue;
                }
                $downloadArr = explode("人", $v['1']);
                $downloadNum = (int)$downloadArr[0];
                if (strstr($downloadArr[0], "万")) {
                    $downloadNum = $downloadNum * 10000;
                }
                if ($downloadNum == 0) {
                    $downloadNum = rand(1000, 99999);
                }
                $app->download_count = $downloadNum;
                $app->save();
            }
        } catch (\Exception $e) {
            dd($e->getLine(), $e->getMessage());
        }
    }

    public function kuaishouAds($params)
    {
        if (KuaishouAds::where('oaid2', $params['oaid2'])->count() === 0) {
            KuaishouAds::create($params);
        }
        return $this->returnData();
    }

    // 回调快手
    public function callKuaishouAds($oaid, $type)
    {
        if ($data = KuaishouAds::where('oaid2', md5($oaid))->first()) {
            if (($data->status == 1 && $type == 1) || ($data->status == 2 && $type == 2)) {
                // 更新数据库
                if ($type == 1) {
                    $data->status = 2;
                } elseif ($type == 2) {
                    $data->status = 3;
                }
                $data->save();
                // 回调
                $now = time() * 1000;
                $url = "{$data->callback}&event_type={$type}&event_time={$now}";
                $res = Http::get($url);
            }
        }
    }
}
