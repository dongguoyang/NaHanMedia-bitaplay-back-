<?php

namespace App\Models;

class Area extends BaseAuth
{
    public function child()
    {
        return $this->hasMany(Area::class, 'pid');
    }
}
