<?php


namespace App\Srv\Admin;


use App\Models\HotWord;
use App\Srv\Srv;

class HotWordSrv extends Srv
{
    public function list()
    {
        $list = HotWord::orderBy('id', 'desc')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function save($name)
    {
        if (HotWord::where('name', $name)->count()) {
            return $this->returnData(ERR_PARAM_ERR, '已添加');
        }
        HotWord::create(['name'=>$name]);
        return $this->returnData();
    }

    public function del($id)
    {
        HotWord::destroy($id);
        return $this->returnData();
    }
}
