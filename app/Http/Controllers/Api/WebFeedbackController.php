<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Srv\Api\WebFeedbackSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebFeedbackController extends Controller
{
    public function feedback(Request $request, WebFeedbackSrv $srv)
    {
        $p = $request->only('tel', 'name', 'content');
        $validator = Validator::make($p, [
            'tel' => 'required',
            'content' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->feedback($p));
    }
}

