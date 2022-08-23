<?php


namespace App\Srv\Admin;

use App\Models\KuaishouAds;
use App\Models\ProviderApp;
use App\Models\ProviderAppChainRecord;
use App\Models\ProviderRechargeBalanceRecord;
use App\Models\User;
use App\Models\UserDownloadRecord;
use App\Models\UserThirdLoginRecord;
use App\Srv\Srv;

class DashboardSrv extends Srv
{
    public function index()
    {
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-31 23:59:59');

        // 用户数量
        $data['user']['total'] = User::count();
        $data['user']['today'] = User::where('created_at', '<=', $todayEnd)
            ->where('created_at', '>=', $todayStart)->count();
        $data['user']['month'] = User::where('created_at', '<=', $monthEnd)
            ->where('created_at', '>=', $monthStart)->count();

        // 上架应用数量
        $data['app']['total'] = ProviderApp::where('status', APP_STATUS_ABLE)->count();
        $data['app']['today'] = ProviderApp::where('status', APP_STATUS_ABLE)->where('created_at', '<=', $todayEnd)
            ->where('created_at', '>=', $todayStart)->count();
        $data['app']['month'] = ProviderApp::where('status', APP_STATUS_ABLE)->where('created_at', '<=', $monthEnd)
            ->where('created_at', '>=', $monthStart)->count();

        // 三方登录数量
        $data['third_login']['total'] = UserThirdLoginRecord::count();
        $data['third_login']['today'] = UserThirdLoginRecord::where('created_at', '<=', $todayEnd)
            ->where('created_at', '>=', $todayStart)->count();
        $data['third_login']['month'] = UserThirdLoginRecord::where('created_at', '<=', $monthEnd)
            ->where('created_at', '>=', $monthStart)->count();

        // 燃料燃烧数量
        $data['fuel']['total'] = ProviderAppChainRecord::sum('fuel');
        $data['fuel']['today'] = ProviderAppChainRecord::where('created_at', '<=', $todayEnd)
            ->where('created_at', '>=', $todayStart)->sum('fuel');
        $data['fuel']['month'] = ProviderAppChainRecord::where('created_at', '<=', $monthEnd)
            ->where('created_at', '>=', $monthStart)->sum('fuel');

        // 下载数量
        $data['download']['total'] = UserDownloadRecord::count();
        $data['download']['today'] = UserDownloadRecord::where('created_at', '<=', $todayEnd)
            ->where('created_at', '>=', $todayStart)->count();
        $data['download']['month'] = UserDownloadRecord::where('created_at', '<=', $monthEnd)
            ->where('created_at', '>=', $monthStart)->count();

        // 充值金额
        $data['recharge']['total'] = ProviderRechargeBalanceRecord::sum('amount');
        $data['recharge']['today'] = ProviderRechargeBalanceRecord::where('created_at', '<=', $todayEnd)
            ->where('created_at', '>=', $todayStart)->sum('amount');
        $data['recharge']['month'] = ProviderRechargeBalanceRecord::where('created_at', '<=', $monthEnd)
            ->where('created_at', '>=', $monthStart)->sum('amount');

        return $this->returnData(ERR_SUCCESS, '', $data);
    }

    public function kuaishouAds()
    {
        // 展示数量
//        $data['show'] = KuaishouAds::count();
        // 激活
        $data['download'] = KuaishouAds::where('status', '>', 1)->count();
        // 注册
        $data['register'] = KuaishouAds::where('status', '>=2')->count();
        return $this->returnData(ERR_SUCCESS, '', $data);
    }
}
