<?php


namespace App\Srv\Admin;

use App\Models\Industry;
use App\Models\Occupation;
use App\Srv\Srv;

class OccupationSrv extends Srv
{
    public function list($p)
    {
        $query = Occupation::with('industry')->orderBy('id', 'desc');
        if ($p['name']) {
            $query->where('name', 'like', "%{$p['name']}%");
        }
        if ($p['industry_id'] > 0) {
            $query->where('industry_id', $p['industry_id']);
        }
        $list = $query->orderBy('id', 'desc')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }


    public function save($p)
    {
        if (Industry::where('id', $p['industry_id'])->count() == 0) {
            return $this->returnData(ERR_PARAM_ERR, '请选择行业');
        }
        $occupation = Occupation::where(['industry_id' => $p['industry_id'], 'name' => $p['name']])->first();
        if ($occupation && $occupation->id != $p['id']) {
            return $this->returnData(ERR_PARAM_ERR, '已存在');
        }
        $occupation = Occupation::where('id', $p['id'])->first();
        if (!$occupation) {
            $occupation = new Occupation();
        }
        $occupation->industry_id = $p['industry_id'];
        $occupation->name = $p['name'];
        $occupation->save();
        return $this->returnData();
    }
}
