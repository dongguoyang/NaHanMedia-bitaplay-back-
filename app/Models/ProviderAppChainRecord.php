<?php

namespace App\Models;

class ProviderAppChainRecord extends Base
{
    protected $fillable = ['provider_app_id', 'number', 'content', 'hash', 'fuel'];

    public function app()
    {
        return $this->belongsTo(ProviderApp::class, 'provider_app_id');
    }
}
