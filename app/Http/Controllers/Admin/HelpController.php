<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Srv\Admin\HelpSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HelpController extends Controller
{
    public function list(Request $request, HelpSrv $srv)
    {
        $p = $request->only('type', 'status');
        $validator = Validator::make($p, [
            'type' => 'required|in:1,2',
            'status' => 'required|in:0,1,2'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->list($p));
    }

    public function handle(Request $request, HelpSrv $srv)
    {
        $p = $request->only('id');
        $validator = Validator::make($p, [
            'id' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->handle($p['id']));
    }
}
