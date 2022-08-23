<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThirdProviderSeeder extends Seeder
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
            // 注册三方软件提供商
            $p = \App\Models\Provider::create([
                'pid' => 0,
                'tel' => '13111111112',
                'email' => 'thirdprovider@bitaplay.com',
                'token' => md5('13111111112'),
                'status' => 2,
            ]);
            // 注册钱包
            \App\Models\ProviderWallet::create([
                'provider_id' => $p->id
            ]);
            // 认证
            \App\Models\ProviderInfo::create([
                'provider_id' => $p->id,
                'code' => '938389483494',
                'name' => '敲门科技',
                'license' => 'https://knockdoor-bita.oss-cn-chengdu.aliyuncs.com/7852217ece3df8abccc6c103c9e0a889.png',
                'status' => 2,
                'role' => 1,
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
}
