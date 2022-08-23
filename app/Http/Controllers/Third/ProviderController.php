<?php


namespace App\Http\Controllers\Third;


use App\Http\Controllers\Controller;
use App\Srv\Third\ProviderSrv;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function toChain(Request $request, ProviderSrv $srv)
    {
        $p = $request->all();
        return $this->responseDirect($srv->toChain($p));
    }
}
