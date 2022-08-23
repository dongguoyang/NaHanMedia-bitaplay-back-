<?php

namespace App\Models;

class UserWallet extends Base
{
    protected $fillable = ['user_id', 'balance', 'frozen_balance'];
    protected $primaryKey = 'user_id';
}
