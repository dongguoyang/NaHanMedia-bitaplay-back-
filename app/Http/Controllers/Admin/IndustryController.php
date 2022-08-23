<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\IndustrySrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IndustryController extends Controller
{
    public function list(Request $request, IndustrySrv $srv)
    {
        return $this->responseDirect($srv->list($request->input('name', '')));
    }

    public function all(IndustrySrv $srv)
    {
        return $this->responseDirect($srv->all());
    }

    public function save(Request $request, IndustrySrv $srv)
    {
        $p = $request->only('id', 'name');
        $validator = Validator::make($p, [
            'id' => 'present',
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->save($p));
    }
}
