<?php

namespace App\Models;

use Illuminate\Support\Facades\Crypt;

class Admin extends BaseAuth
{
    protected $table = 'admin';
    protected $fillable = ['username', 'password', 'nickname', 'avatar', 'token'];


    public function getPasswordAttribute($val)
    {
        return Crypt::decrypt($val);
    }

    public function setPasswordAttribute($val)
    {
        $this->attributes['password'] = Crypt::encrypt($val);
    }
}
