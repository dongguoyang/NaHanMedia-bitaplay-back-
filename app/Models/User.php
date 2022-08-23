<?php

namespace App\Models;

class User extends BaseAuth
{
    protected $fillable = ['tel', 'code', 'pid', 'trans_password', 'nickname', 'avatar', 'email', 'desc', 'token', 'status'];

    protected $hidden = ['trans_password'];

    protected $appends = ['is_set_trans_password'];

    public function info()
    {
        return $this->hasOne(UserInfo::class);
    }

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

    public function wallet()
    {
        return $this->hasOne(UserWallet::class);
    }

    public function withdrawAccount()
    {
        return $this->hasOne(UserWithdrawAccount::class);
    }

    public function app()
    {
        return $this->hasMany(ProviderAppUsers::class, 'user_id');
    }

    public function getIsSetTransPasswordAttribute()
    {
        return $this->attributes['is_set_trans_password'] = $this->trans_password ? 1 : 0;
    }
}
