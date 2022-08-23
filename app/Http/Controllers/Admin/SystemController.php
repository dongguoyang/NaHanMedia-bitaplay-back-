<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\SystemSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SystemController extends Controller
{
    public function search(Request $request, SystemSrv $srv)
    {
        return $this->responseDirect($srv->search($request->input('key', '')));
    }

    public function save(Request $request, SystemSrv $srv)
    {
        $p = $request->only('key', 'value');
        $validator = Validator::make($p, [
            'key' => 'required',
            'value' => 'required|array'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->save($p['key'], $p['value']));
    }

    public function agreement(SystemSrv $srv)
    {
        return $this->responseDirect($srv->agreement());
    }
}
