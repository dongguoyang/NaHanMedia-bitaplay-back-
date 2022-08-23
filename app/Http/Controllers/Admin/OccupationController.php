<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\OccupationSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OccupationController extends Controller
{
    public function list(Request $request, OccupationSrv $srv)
    {
        $p = $request->only('industry_id', 'name');
        $validator = Validator::make($p, [
            'industry_id' => 'present',
            'name' => 'present',
        ]);
        return $this->responseDirect($srv->list($p));
    }


    public function save(Request $request, OccupationSrv $srv)
    {
        $p = $request->only('id', 'name', 'industry_id');
        $validator = Validator::make($p, [
            'id' => 'required',
            'name' => 'required',
            'industry_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->save($p));
    }
}
