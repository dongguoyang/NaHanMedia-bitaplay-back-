<?php

namespace App\Models;

class ProviderAppRechargeFuelRecord extends BaseAuth
{
    protected $fillable = ['provider_id', 'number', 'provider_app_id', 'amount','price','fuel_amount'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function app()
    {
        return $this->belongsTo(ProviderApp::class,'provider_app_id');
    }
}
