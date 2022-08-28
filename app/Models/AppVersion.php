<?php

namespace App\Models;

class AppVersion extends BaseAuth{

    protected $table = 'app_version';

    protected $fillable = ['url', 'version', 'remark','status'];


}