<?php

namespace App\Models;

class Provider extends BaseAuth
{
    protected $fillable = ['tel', 'pid', 'trans_password', 'email', 'token', 'status'];

    protected $hidden = ['trans_password'];

    public function getTransPasswordAttribute($val)
    {
        if ($val) {
            return decrypt($val);
        }
        return $val;
    }

    public function setTransPasswordAttribute($val)
    {
        $this->attributes['trans_password'] = encrypt($val);
    }

    public function info()
    {
        return $this->hasOne(ProviderInfo::class);
    }

    public function wallet()
    {
        return $this->hasOne(ProviderWallet::class);
    }


    public function withdrawAccount()
    {
        return $this->hasOne(ProviderWithdrawAccount::class, 'provider_id');
    }

    public function app()
    {
        return $this->hasMany(ProviderApp::class,'provider_id');
    }
}
