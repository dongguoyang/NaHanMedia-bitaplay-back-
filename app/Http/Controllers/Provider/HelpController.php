<?php


namespace App\Http\Controllers\Provider;


use App\Http\Controllers\Controller;
use App\Srv\Provider\HelpSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HelpController extends Controller
{
    public function help(Request $request, HelpSrv $srv)
    {
        $p = $request->only('type');
        $validator = Validator::make($p, [
            'type' => 'required|in:1,2',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->help($p['type']));
    }
}
