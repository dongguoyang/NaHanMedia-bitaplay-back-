<?php

namespace App\Models;

class AppCategory extends BaseAuth
{
    protected $fillable = ['pid', 'name'];

    public function child()
    {
        return $this->hasMany(AppCategory::class, 'pid');
    }

    public function parent()
    {
        return $this->belongsTo(AppCategory::class,'pid');
    }
}
