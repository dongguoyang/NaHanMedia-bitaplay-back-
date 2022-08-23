<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\WebFeedbackSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebFeedbackController extends Controller
{
    public function list(Request $request, WebFeedbackSrv $srv)
    {
        return $this->responseDirect($srv->list($request->input('status', 0)));
    }

    public function handel(Request $request, WebFeedbackSrv $srv)
    {
        $p = $request->only('id', 'remark');
        $validator = Validator::make($p, [
            'id' => 'required|min:1',
            'remark' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '请填写备注');
        }
        return $this->responseDirect($srv->handel($p['id'], $p['remark']));
    }
}
