<?php


namespace App\Srv\Admin;


use App\Models\Avatar;
use App\Srv\Srv;
use Illuminate\Support\Facades\DB;

class AvatarSrv extends Srv
{
    public function list()
    {
        $list = Avatar::orderBy('id', 'desc')->paginate(10);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function save($p)
    {
        try {
            DB::beginTransaction();
            foreach ($p as $v) {
                Avatar::create(['url' => $v]);
            }
            DB::commit();
            return $this->returnData();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnData(ERR_FAILED, '服务器错误');
        }
    }


    public function del($id)
    {
        Avatar::where('id', $id)->delete();
        return $this->returnData();
    }
}
