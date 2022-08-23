<?php

namespace App\Models;

class UserDevice extends Base
{
    protected $fillable = ['user_id', 'name', 'ip','system'];
}
