<?php


namespace App\Srv\Provider;

use App\Models\Help;
use App\Srv\Srv;

class HelpSrv extends Srv
{
    public function help($type)
    {
        $provider = $this->getProvider();
        if (Help::where(['provider_id' => $provider->id, 'tel' => $provider->tel, 'status' => 1, 'type' => $type])->count() == 0) {
            Help::create([
                'provider_id' => $provider->id,
                'tel' => $provider->tel,
                'status' => 1,
                'type' => $type
            ]);
        }
        return $this->returnData();
    }
}
