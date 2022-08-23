<?php

namespace App\Models;

class ProviderWithdrawAccount extends BaseAuth
{
    protected $fillable = ['provider_id', 'bank_name', 'bank_account_name', 'bank_number', 'alipay_account_name', 'alipay_number'];



}
