<?php

namespace App\Models;

class Feedback extends Base
{
    protected $fillable = ['user_id', 'content', 'image', 'status', 'remark'];

    protected $table = "feedbacks";

    public function getImageAttribute($val)
    {
        $image = explode(",", $val);
        if($image[0]==""){
            unset($image[0]);
        }
        return $image;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
