<?php

use Illuminate\Database\Seeder;

class AndroidShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $list = [
            [
                'shop_name' => '腾讯应用宝',
                'package_name' => 'com.tencent.android.qqdownloader',
            ],
            [
                'shop_name' => '360手机助手',
                'package_name' => 'com.qihoo.appstore',
            ],
            [
                'shop_name' => '百度手机助手',
                'package_name' => 'com.baidu.appsearch',
            ],
            [
                'shop_name' => '小米应用商店',
                'package_name' => 'com.xiaomi.market',
            ],
            [
                'shop_name' => '华为应用商店',
                'package_name' => 'com.huawei.appmarket',
            ],
            [
                'shop_name' => '豌豆荚',
                'package_name' => 'com.wandoujia.phoenix2',
            ],
            [
                'shop_name' => '91手机助手',
                'package_name' => 'com.dragon.android.pandaspace',
            ],
            [
                'shop_name' => '安智应用商店',
                'package_name' => 'com.hiapk.marketpho',
            ],
            [
                'shop_name' => '应用汇',
                'package_name' => 'com.yingyonghui.market',
            ],
            [
                'shop_name' => 'QQ手机管家',
                'package_name' => 'com.tencent.qqpimsecure',
            ],
            [
                'shop_name' => '机锋应用市场',
                'package_name' => 'com.mappn.gfan',
            ],
            [
                'shop_name' => 'PP手机助手',
                'package_name' => 'com.pp.assistant',
            ],
            [
                'shop_name' => 'OPPO应用商店',
                'package_name' => 'com.oppo.market',
            ],
            [
                'shop_name' => 'GO市场',
                'package_name' => 'cn.goapk.market',
            ],
            [
                'shop_name' => '中兴应用商店',
                'package_name' => 'zte.com.market',
            ],
            [
                'shop_name' => '宇龙Coolpad应用商店',
                'package_name' => 'com.yulong.android.coolmart',
            ],
            [
                'shop_name' => '联想应用商店',
                'package_name' => 'com.lenovo.leos.appstore',
            ],
            [
                'shop_name' => 'cool市场',
                'package_name' => 'com.coolapk.market',
            ],
        ];
        foreach ($list as $v) {
            \App\Models\AndroidShop::create($v);
        }
    }
}
