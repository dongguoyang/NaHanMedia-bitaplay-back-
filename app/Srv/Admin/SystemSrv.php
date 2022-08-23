<?php


namespace App\Srv\Admin;

use App\Models\System;
use App\Srv\Srv;

class SystemSrv extends Srv
{
    public function search($key)
    {
        $system = System::where('key', $key)->first();
        return $this->returnData(ERR_SUCCESS, '', $system['value']);
    }

    public function save($key, $value)
    {
        if (!$system = System::where('key', $key)->first()) {
            return $this->returnData(ERR_PARAM_ERR, '非法操作');
        }
        $system->value = $value;
        $system->save();
        return $this->returnData();
    }

    public function agreement()
    {
        $user = $this->search('user_agreement');
        $privacy = $this->search('privacy_agreement');
        $provider = $this->search('provider_agreement');
        $data['user_agreement'] = $user['data'];
        $data['privacy_agreement'] = $privacy['data'];
        $data['provider_agreement'] = $provider['data'];
        return $this->returnData(ERR_SUCCESS, '', $data);
    }
}
