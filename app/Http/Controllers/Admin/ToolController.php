<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\ToolSrv;
use App\Srv\Utils\UploadSrv;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function uploadImage(Request $request, UploadSrv $srv)
    {
        if (!$request->hasFile('file')) {
            return $this->response(ERR_PARAM_ERR, '请上传图片');
        }
        return $this->responseDirect($srv->uploadImage($request->file('file')));
    }

    public function kuaishouAds(ToolSrv $srv)
    {
        return $this->responseDirect($srv->kuaishouAds());
    }
}

