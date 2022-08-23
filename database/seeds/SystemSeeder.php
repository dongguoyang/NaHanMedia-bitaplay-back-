<?php

use Illuminate\Database\Seeder;

class SystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 推荐奖励
        \App\Models\System::create([
            'key' => 'recommend_reward',
            'value' => [
                'amount' => 10,
            ],
            'comment' => '推荐奖励金额'
        ]);
        // 最低下载奖励金额
        \App\Models\System::create([
            'key' => 'lowest_download_reward',
            'value' => [
                'amount' => '10'
            ],
            'comment' => '最低下载奖励金额'
        ]);
        // 燃料价格
        \App\Models\System::create([
            'key' => 'fuel_price',
            'value' => [
                'price' => '10',
            ],
            'comment' => '燃料价格'
        ]);
        // 三方登录价格
        \App\Models\System::create([
            'key' => 'third_login_price',
            'value' => [
                'price' => '10',
            ],
            'comment' => '三方登录价格'
        ]);

        \App\Models\System::create([
            'key' => 'fee',
            'value' => [
                'withdraw_fee' => 0.0075,
                'provider_withdraw_fee' => 0.006,
            ],
            'comment' => '手续费'
        ]);


        \App\Models\System::create([
            'key' => 'user_agreement',
            'value' => [
                'content' => '用户协议',
            ],
            'comment' => '用户协议'
        ]);

        \App\Models\System::create([
            'key' => 'privacy_agreement',
            'value' => [
                'content' => '隐私协议',
            ],
            'comment' => '隐私协议'
        ]);

        \App\Models\System::create([
            'key' => 'provider_agreement',
            'value' => [
                'content' => '开发协议',
            ],
            'comment' => '开发协议'
        ]);

        \App\Models\System::create([
            'key' => 'ad_status',
            'value' => [
                'count' => 1000,
                'ended_at' => '2023-10-10 00:00:00'
            ],
            'comment' => '免DID广告收益开通'
        ]);
    }
}
