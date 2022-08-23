<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\FeedbackSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    public function list(Request $request, FeedbackSrv $srv)
    {
        return $this->responseDirect($srv->list($request->input('status', 0)));
    }

    public function handel(Request $request, FeedbackSrv $srv)
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
