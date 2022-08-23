<?php


namespace App\Srv\Provider;


use App\Models\Provider;
use App\Models\ProviderInfo;
use App\Srv\Srv;
use App\Srv\Utils\SmsSrv;

class RemindSrv extends Srv
{
    public function sendRemindProvider($providerId, $function, $amount)
    {
        $provider = Provider::where('id', $providerId)->first();
        $info = ProviderInfo::where('provider_id', $providerId)->first();
        (new SmsSrv())->sendRemindProvider($provider->tel, $info->name, $function, "{$amount}次");
    }
}
