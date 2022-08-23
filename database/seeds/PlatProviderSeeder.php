<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlatProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            DB::beginTransaction();
            $provider = \App\Models\Provider::create([
                'tel' => '13111111111',
                'pid' => 0,
                'token' => md5('13111111111'),
                'status' => PROVIDER_STATUS_ABLE
            ]);
            $pid = $provider->id;
            \App\Models\ProviderInfo::create([
                'provider_id' => $pid,
                'code' => 'pingtairuanjianshang',
                'name' => '平台软件商',
                'license' => 'https://newknockdoor.oss-cn-chengdu.aliyuncs.com/portrait/202110/9be90bb7f7070aa24fa2a4c8f15f1840.jpg',
                'status' => PROVIDER_INFO_STATUS_ABLE,
            ]);
            $app = \App\Models\ProviderApp::create([
                'provider_id' => $pid,
                'app_id' => md5(1),
                'app_key' => md5($pid . time()),
                'app_secret' => substr(md5($pid . time()), 0, 16),
                'icon' => 'https://newknockdoor.oss-cn-chengdu.aliyuncs.com/portrait/202110/9be90bb7f7070aa24fa2a4c8f15f1840.jpg',
                'version' => '1.0.0',
                'name' => 'BitaPlay软件商管理后台',
                'desc' => 'BitaPlay软件商管理后台',
                'image' => json_encode([]),
                'web' => 'business.bitaplay.com',
                'is_web' => 1,
                'status' => APP_STATUS_ABLE,
                'is_third_login' => 1,
                'third_login_amount' => 100000000000000,
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
            ]);
            \App\Models\ProviderAppVersion::create([
                'provider_id' => $pid,
                'provider_app_id' => $app->id,
                'icon' => 'https://newknockdoor.oss-cn-chengdu.aliyuncs.com/portrait/202110/9be90bb7f7070aa24fa2a4c8f15f1840.jpg',
                'version' => '1.0.0',
                'desc' => 'BitaPlay软件商管理后台',
                'image' => json_encode([]),
                'web' => 'business.bitaplay.com',
                'is_web' => 1,
                'status' => APP_VERSION_STATUS_ABLE,
            ]);
            \App\Models\ProviderWallet::create(['provider_id' => $pid]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }
}
