<?php


namespace App\Srv\Admin;

use App\Models\Area;
use App\Models\Industry;
use App\Models\KuaishouAds;
use App\Srv\Srv;

class ToolSrv extends Srv
{
    public function kuaishouAds()
    {
        $data = KuaishouAds::orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($data));
    }
}
