<?php


namespace App\Srv\Admin;

use App\Models\AppGrade;
use App\Models\Industry;
use App\Srv\Srv;

class IndustrySrv extends Srv
{
    public function list($name)
    {
        $query = Industry::orderBy('id', 'desc');
        if ($name) {
            $query->where('name', 'like', "%{$name}%");
        }
        $list = $query->orderBy('id', 'desc')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function all()
    {
        $list = Industry::get();
        return $this->returnData(ERR_SUCCESS, '', $list);
    }

    public function save($p)
    {
        $industry = Industry::where('name', $p['name'])->first();
        if ($industry && $industry->id != $p['id']) {
            return $this->returnData(ERR_PARAM_ERR, '已存在');
        }
        $industry = Industry::where('id', $p['id'])->first();
        if (!$industry) {
            $industry = new Industry();
        }
        $industry->name = $p['name'];
        $industry->save();
        return $this->returnData();
    }
}
