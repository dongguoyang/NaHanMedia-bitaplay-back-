<?php

namespace App\Models;

class ProviderInfo extends BaseAuth
{
    protected $fillable = ['provider_id', 'code', 'name', 'license', 'status', 'refuse_reason', 'role', 'id_card_face', 'id_card_back'];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
