<?php

namespace App\Models;

class UserWithdrawAccount extends Base
{
    protected $fillable = ['user_id', 'bank_name', 'bank_account_name', 'bank_number', 'alipay_account_name', 'alipay_number'];
}
