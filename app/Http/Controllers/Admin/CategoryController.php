<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\CategorySrv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function list(Request $request, CategorySrv $srv)
    {
        return $this->responseDirect($srv->list($request->get("name", '')));
    }

    public function add(Request $request, CategorySrv $srv)
    {
        $name = $request->get("name");
        $pid = $request->get('pid');
        return $this->responseDirect($srv->add($pid, $name));
    }

    public function tree(CategorySrv $srv)
    {
        return $this->responseDirect($srv->tree());
    }

    public function cascaderTree(CategorySrv $srv)
    {
        return $this->responseDirect($srv->cascaderTree());
    }
}
