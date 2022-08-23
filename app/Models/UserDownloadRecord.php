<?php

namespace App\Models;

class UserDownloadRecord extends Base
{
    protected $fillable = ['user_id', 'provider_app_id', 'number', 'reward_amount', 'user_reward_amount', 'status'];

    public function app()
    {
        return $this->belongsTo(ProviderApp::class, 'provider_app_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->hasMany(ProviderAppCategory::class, 'provider_app_id', 'provider_app_id');
    }
}
