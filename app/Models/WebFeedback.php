<?php

namespace App\Models;

class WebFeedback extends Base
{
    protected $fillable = ['tel', 'name', 'content', 'status', 'remark'];

    protected $table = "web_feedback";

}
