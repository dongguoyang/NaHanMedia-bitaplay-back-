<?php

namespace App\Models;

class ProviderAppUsers extends Base
{
    protected $table = 'provider_app_users';
    protected $fillable = ['provider_app_id', 'user_id', 'open_id'];

}
