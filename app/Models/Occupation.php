<?php

namespace App\Models;

class Occupation extends Base
{
    protected $fillable = ['industry_id', 'name'];

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }
}
