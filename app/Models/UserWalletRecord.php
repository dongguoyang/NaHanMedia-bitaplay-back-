<?php

namespace App\Models;

class UserWalletRecord extends Base
{
    protected $fillable = ['user_id', 'order_id', 'number', 'payment_method', 'amount', 'type','status'];
}
