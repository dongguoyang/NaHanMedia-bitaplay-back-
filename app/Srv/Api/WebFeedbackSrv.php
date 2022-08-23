<?php


namespace App\Srv\Api;


use App\Models\WebFeedback;
use App\Srv\Srv;

class WebFeedbackSrv extends Srv
{
    public function feedback($p)
    {
        WebFeedback::create($p);
        return $this->returnData();
    }
}

