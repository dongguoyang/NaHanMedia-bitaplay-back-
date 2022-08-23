<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\HotWordSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HotWordController extends Controller
{
    public function list(HotWordSrv $srv)
    {
        return $this->responseDirect($srv->list());
    }

    public function save(Request $request, HotWordSrv $srv)
    {
        $p = $request->only('name');
        $validator = Validator::make($p, [
            'name' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->save($p['name']));
    }

    public function del(Request $request, HotWordSrv $srv)
    {
        return $this->responseDirect($srv->del($request->input('id')));
    }
}
