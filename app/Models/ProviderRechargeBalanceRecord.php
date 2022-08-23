<?php

namespace App\Models;

class ProviderRechargeBalanceRecord extends BaseAuth
{
    protected $fillable = ['provider_id', 'number', 'amount', 'payment_method', 'payment_number', 'status'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
