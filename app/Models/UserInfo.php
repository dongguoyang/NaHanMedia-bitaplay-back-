<?php

namespace App\Models;

class UserInfo extends Base
{
    protected $fillable = ['user_id', 'name', 'id_number','id_face','id_back', 'province', 'city', 'county', 'industry_id', 'occupation_id', 'educational_experience','address', 'hash','sex','age','ad_status','birthday'];

    public function getEducationalExperienceAttribute($val)
    {
        return json_decode($val, true);
    }

    public function getAddressAttribute($val)
    {
        return json_decode($val, true);
    }

    public function occupation()
    {
        return $this->belongsTo(Occupation::class);
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }
}
