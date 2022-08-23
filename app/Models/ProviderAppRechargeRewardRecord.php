<?php

namespace App\Models;

class ProviderAppRechargeRewardRecord extends BaseAuth
{
    protected $fillable = ['provider_id', 'number', 'provider_app_id', 'amount'];


    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function app()
    {
        return $this->belongsTo(ProviderApp::class,'provider_app_id');
    }
}
