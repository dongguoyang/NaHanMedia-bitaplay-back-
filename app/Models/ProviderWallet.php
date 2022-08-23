<?php

namespace App\Models;

class ProviderWallet extends Base
{
    protected $fillable = ['provider_id', 'balance', 'frozen_balance'];
    protected $primaryKey = 'provider_id';
}
