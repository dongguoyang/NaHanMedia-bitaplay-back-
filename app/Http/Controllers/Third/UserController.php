<?php


namespace App\Http\Controllers\Third;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Srv\Third\UserSrv;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function info(Request $request, UserSrv $srv)
    {
        $p = $request->all();
        return $this->responseDirect($srv->info($p));
    }

    public function webLogin(Request $request, UserSrv $srv)
    {
        $p = $request->only('app_id', 'grant_code', 'callback_url');
        $validator = Validator::make($p, [
            'app_id' => 'required|size:32',
            'grant_code' => 'required',
            'callback_url' => 'required|url'
        ]);
        if ($validator->fails()) {
            return $this->response(ERR_PARAM_ERR, $validator->failed());
        }
        return $this->responseDirect($srv->webLogin($p));
    }


}
