<?php

namespace App\Models;

class KuaishouAds extends BaseAuth
{
    protected $table = 'kuaishou_ads';

    protected $fillable = ['account_id', 'aid', 'cid', 'did', 'ts', 'ip','callback','oaid','oaid2'];
}
