<?php

namespace App\Models;

class UserThirdLoginRecord extends Base
{
    protected $fillable = ['user_id', 'provider_app_id', 'ip','nickname','avatar'];

    public function appUser()
    {
        return $this->belongsTo(ProviderAppUsers::class, 'user_id', 'user_id');
    }

    public function app()
    {
        return $this->belongsTo(ProviderApp::class, 'provider_app_id');
    }
}

