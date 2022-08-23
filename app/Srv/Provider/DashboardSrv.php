<?php


namespace App\Srv\Provider;

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
        $provider = $this->getProvider();
        $id = $provider->id;

        // 三方登录
        $data['login']['total'] = UserThirdLoginRecord::whereHas('app', function ($query) use ($id) {
            $query->where('provider_id', $id);
        })->count();
        $data['login']['today'] = UserThirdLoginRecord::where('created_at', '>=', $todayStart)
            ->where('created_at', '<=', $todayEnd)
            ->whereHas('app', function ($query) use ($id) {
                $query->where('provider_id', $id);
            })->count();
        $data['login']['month'] = UserThirdLoginRecord::where('created_at', '>=', $monthStart)
            ->where('created_at', '<=', $monthEnd)
            ->whereHas('app', function ($query) use ($id) {
                $query->where('provider_id', $id);
            })->count();

        // 上链
        $data['chain']['total'] = ProviderAppChainRecord::whereHas('app', function ($query) use ($id) {
            $query->where('provider_id', $id);
        })->count();
        $data['chain']['today'] = ProviderAppChainRecord::where('created_at', '>=', $todayStart)
            ->where('created_at', '<=', $todayEnd)
            ->whereHas('app', function ($query) use ($id) {
                $query->where('provider_id', $id);
            })->count();
        $data['chain']['month'] = ProviderAppChainRecord::where('created_at', '>=', $monthStart)
            ->where('created_at', '<=', $monthEnd)
            ->whereHas('app', function ($query) use ($id) {
                $query->where('provider_id', $id);
            })->count();

        // 下载
        $data['download']['total'] = UserDownloadRecord::whereHas('app', function ($query) use ($id) {
            $query->where('provider_id', $id);
        })->count();
        $data['download']['today'] = UserDownloadRecord::where('created_at', '>=', $todayStart)
            ->where('created_at', '<=', $todayEnd)
            ->whereHas('app', function ($query) use ($id) {
                $query->where('provider_id', $id);
            })->count();
        $data['download']['month'] = UserDownloadRecord::where('created_at', '>=', $monthStart)
            ->where('created_at', '<=', $monthEnd)
            ->whereHas('app', function ($query) use ($id) {
                $query->where('provider_id', $id);
            })->count();


        return $this->returnData(ERR_SUCCESS, '', $data);
    }
}
