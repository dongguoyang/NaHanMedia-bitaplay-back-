<?php

namespace App\Models;

class ProviderWithdraw extends BaseAuth
{
    protected $fillable = ['provider_id', 'number', 'amount', 'name', 'bank_name', 'bank_number', 'alipay_number', 'payment_method', 'status', 'evidence', 'refuse_reason','fee'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
