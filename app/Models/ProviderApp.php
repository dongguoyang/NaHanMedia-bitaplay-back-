<?php

namespace App\Models;

class ProviderApp extends BaseAuth
{
    protected $fillable = ['id', 'provider_id', 'app_id', 'app_key', 'app_secret', 'version', 'name', 'ios_package_name', 'package_name', 'desc', 'image', 'web', 'ios', 'is_ios', 'is_android', 'is_web', 'status', 'download_reward', 'reward_amount', 'fuel_amount', 'third_login_amount', 'icon', 'recommend_city', 'recommend_industry', 'remind_third_login', 'remind_reward', 'remind_fuel', 'recommend_sex', 'recommend_age', 'recommend_preference', 'recommend_style', 'recommend_educational', 'recommend_device', 'recommend_system', 'recommend_real', 'recommend_week_download', 'recommend_month_download', 'banner','third_category','download_reward_status','download_count'];


    public function getImageAttribute($val)
    {
        return json_decode($val, true);
    }

    public function setImageAttribute($val)
    {
        $this->attributes['image'] = json_encode($val);
    }

    public function getRecommendPreferenceAttribute($val)
    {
        return json_decode($val, true);
    }

    public function setRecommendPreferenceAttribute($val)
    {
        $this->attributes['recommend_preference'] = json_encode($val);
    }


    public function versions()
    {
        return $this->hasMany(ProviderAppVersion::class);
    }

    public function shop()
    {
        return $this->belongsToMany(AndroidShop::class, 'provider_app_shops');
    }

    public function l1()
    {
        return $this->belongsToMany(AppCategory::class, 'provider_app_categories', 'provider_app_id', 'l1');
    }

    public function l2()
    {
        return $this->belongsToMany(AppCategory::class, 'provider_app_categories', 'provider_app_id', 'l2');
    }

    public function category()
    {
        return $this->hasMany(ProviderAppCategory::class);
    }

    public function recommendCategory()
    {
        return $this->hasMany(ProviderAppRecommendCategory::class);
    }

    public function l3()
    {
        return $this->belongsToMany(AppCategory::class, 'provider_app_categories', 'provider_app_id', 'l3');
    }

    public function grade()
    {
        return $this->belongsToMany(AppGrade::class, 'provider_app_grades');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function chain()
    {
        return $this->hasMany(ProviderAppChainRecord::class);
    }

    public function download()
    {
        return $this->hasMany(UserDownloadRecord::class);
    }

    public function login()
    {
        return $this->hasMany(UserThirdLoginRecord::class);
    }

    public function collect()
    {
        return $this->hasMany(UserCollect::class);
    }

    public function getRecommendCityAttribute($val)
    {
        return explode(',', $val);
    }

    public function setRecommendCityAttribute($val)
    {
        $this->attributes['recommend_city'] = implode(',', $val);
    }

    public function getRecommendIndustryAttribute($val)
    {
        return explode(',', $val);
    }

    public function setRecommendIndustryAttribute($val)
    {
        $this->attributes['recommend_industry'] = implode(',', $val);
    }

    public function getRecommendStyleAttribute($val)
    {
        return explode(',', $val);
    }

    public function setRecommendStyleAttribute($val)
    {
        $this->attributes['recommend_style'] = implode(',', $val);
    }

    public function getRecommendEducationalAttribute($val)
    {
        return explode(',', $val);
    }

    public function setRecommendEducationalAttribute($val)
    {
        $this->attributes['recommend_educational'] = implode(',', $val);
    }

    public function getRecommendDeviceAttribute($val)
    {
        return explode(',', $val);
    }

    public function setRecommendDeviceAttribute($val)
    {
        $this->attributes['recommend_device'] = implode(',', $val);
    }

    public function getRecommendAgeAttribute($val)
    {
        return explode(',', $val);
    }

    public function setRecommendAgeAttribute($val)
    {
        $this->attributes['recommend_age'] = implode(',', $val);
    }

}
