<?php


namespace App\Srv\Admin;

use App\Models\Help;
use App\Srv\Srv;

class HelpSrv extends Srv
{
    public function list($p)
    {
        $query = Help::orderBy('status')->orderBy('id');
        if ($p['status'] > 0) {
            $query->where('status', $p['status']);
        }
        if ($p['type'] > 0) {
            $query->where('type', $p['type']);
        }
        $list = $query->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function handle($id)
    {
        $help = Help::where('id', $id)->first();
        $help->status = 2;
        $help->save();
        return $this->returnData();
    }
}
