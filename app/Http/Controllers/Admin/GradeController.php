<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\GradeSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GradeController extends Controller
{
    public function list(GradeSrv $srv)
    {
        return $this->responseDirect($srv->list());
    }

    public function save(Request $request, GradeSrv $srv)
    {
        $p = $request->only('id', 'name', 'content');
        $validator = Validator::make($p, [
            'id' => 'present',
            'name' => 'required_if:id,0',
            'content' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->save($p));
    }
}
