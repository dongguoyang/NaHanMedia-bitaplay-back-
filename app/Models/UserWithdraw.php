<?php

namespace App\Models;

class UserWithdraw extends Base
{
    protected $fillable = ['user_id', 'number', 'amount', 'name', 'bank_name', 'bank_number', 'alipay_number', 'payment_method', 'status', 'evidence', 'refuse_reason','fee'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
