<?php


namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Srv\Provider\DashboardSrv;

class DashboardController extends Controller
{
    public function index(DashboardSrv $srv)
    {
        return $this->responseDirect($srv->index());
    }
}
