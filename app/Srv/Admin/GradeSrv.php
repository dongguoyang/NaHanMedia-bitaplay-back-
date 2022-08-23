<?php


namespace App\Srv\Admin;

use App\Models\AppGrade;
use App\Srv\Srv;

class GradeSrv extends Srv
{
    public function list()
    {
        $list = AppGrade::orderBy('id', 'desc')->get();
        return $this->returnData(ERR_SUCCESS, '', $list);
    }

    public function save($p)
    {
        $grade = AppGrade::where('name', $p['name'])->first();
        if ($grade && $grade->id != $p['id']) {
            return $this->returnData(ERR_PARAM_ERR, '已存在');
        }
        $grade = AppGrade::where('id', $p['id'])->first();
        if (!$grade) {
            $grade = new AppGrade();
            $grade->name = $p['name'];
        }
        $grade->content = $p['content'];
        $grade->save();
        return $this->returnData();
    }
}
