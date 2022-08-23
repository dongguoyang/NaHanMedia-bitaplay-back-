<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\SearchWordSrv;
use Illuminate\Http\Request;

class SearchWordController extends Controller
{
    public function list(Request $request, SearchWordSrv $srv)
    {
        return $this->responseDirect($srv->list($request->get('name', '')));
    }
}
