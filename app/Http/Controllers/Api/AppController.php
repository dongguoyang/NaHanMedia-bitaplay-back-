<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Srv\Admin\AppVersionSrv;
use App\Srv\Api\AppSrv;
use App\Srv\Api\UserSrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppController extends Controller
{
    public function list(Request $request, AppSrv $srv)
    {
        $p['is_ios'] = $request->input('is_ios', 0);
        $p['is_android'] = $request->input('is_android', 0);
        $p['category'] = $request->input('category', 0);
        $p['is_match'] = $request->input('is_match', 0);
        $p['uid'] = $request->input('uid', 0);
        $p['name']= $request->input('name','');
        $p['my_collect']= $request->input('my_collect','');
        $p['origin'] = 1;
        $p['page'] = $request->input('page',1);
        return $this->responseDirect($srv->list($p));
    }

    public function detail(Request $request, AppSrv $srv)
    {
        $id = $request->input('id', 0);
        $uid = $request->input('uid', 0);
        return $this->responseDirect($srv->detail($id, $uid));
    }

    public function collect(Request $request, AppSrv $srv)
    {
        return $this->responseDirect($srv->collect($request->input('id', 0)));
    }

    public function download(Request $request, AppSrv $srv)
    {
        $data = $request->input('data', '');
        if ($data == '') {
            return $this->response(ERR_PARAM_ERR, '参数错误');
        }
        return $this->responseDirect($srv->download($data));
    }

    public function getAppVersion(AppVersionSrv $srv)
    {
        return $this->responseDirect($srv->getAppVersion());
    }
}
