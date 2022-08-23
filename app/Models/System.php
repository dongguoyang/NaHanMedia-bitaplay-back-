<?php

namespace App\Models;

class System extends BaseAuth
{
    public function getValueAttribute($val)
    {
        return json_decode($val, true);
    }

    public function setValueAttribute($val)
    {
        $this->attributes['value'] = json_encode($val,JSON_UNESCAPED_UNICODE);
    }
}
