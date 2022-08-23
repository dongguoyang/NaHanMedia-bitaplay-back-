<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Srv\Admin\AvatarSrv;
use Illuminate\Http\Request;

class AvatarController extends Controller
{
    public function list(AvatarSrv $srv)
    {
        return $this->responseDirect($srv->list());
    }

    public function save(Request $request, AvatarSrv $srv)
    {

        return $this->responseDirect($srv->save($request->input('list')));
    }

    public function del(Request $request, AvatarSrv $srv)
    {
        return $this->responseDirect($srv->del($request->input('id')));
    }
}
