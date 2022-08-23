<?php

namespace App\Models;

class UserInviteReward extends BaseAuth
{
    protected $fillable = ['user_id', 'provider_id', 'provider_app_id', 'number', 'amount'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
