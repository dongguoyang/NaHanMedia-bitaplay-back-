<?php


namespace App\Http\Controllers;

use App\Srv\Srv;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function encrypt(Request $request, Srv $srv)
    {
        $code = $request->input('code');
        $secret = $request->input('secret');
        return $this->response(ERR_SUCCESS, '', $srv->encrypt($code, $secret));
    }

    public function decrypt(Request $request, Srv $srv)
    {
        $data = $request->input('data');
        $secret = $request->input('secret');
        return $this->response(ERR_SUCCESS, '', $srv->decrypt($data, $secret));
    }
}



