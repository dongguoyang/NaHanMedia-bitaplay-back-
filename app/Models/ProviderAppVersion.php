<?php

namespace App\Models;

class ProviderAppVersion extends BaseAuth
{
    protected $fillable = ['provider_id', 'provider_app_id', 'version', 'desc', 'ios_package_name','package_name', 'image', 'web', 'ios', 'is_ios', 'is_android', 'is_web', 'status','icon','banner','third_category'];

    public function getImageAttribute($val)
    {
        return json_decode($val, true);
    }

    public function setImageAttribute($val)
    {
        $this->attributes['image'] = json_encode($val);
    }

    public function shop()
    {
        return $this->belongsToMany(AndroidShop::class, 'provider_app_version_shops');
    }

    public function l1()
    {
        return $this->belongsToMany(AppCategory::class, 'provider_app_version_categories', 'provider_app_version_id', 'l1');
    }

    public function l2()
    {
        return $this->belongsToMany(AppCategory::class, 'provider_app_version_categories', 'provider_app_version_id', 'l2');
    }

    public function l3()
    {
        return $this->belongsToMany(AppCategory::class, 'provider_app_version_categories', 'provider_app_version_id', 'l3');
    }

    public function grade()
    {
        return $this->belongsToMany(AppGrade::class, 'provider_app_version_grades');
    }

    public function app()
    {
        return $this->belongsTo(ProviderApp::class,'provider_app_id');
    }
}
