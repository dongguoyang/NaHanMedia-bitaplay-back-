<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Srv\Admin\DashboardSrv;

class DashboardController extends Controller
{
    public function index(DashboardSrv $srv)
    {
        return $this->responseDirect($srv->index());
    }

    public function kuaishouAds(DashboardSrv $srv)
    {
        return $this->responseDirect($srv->kuaishouAds());
    }
}
