<?php
namespace App\Srv\Admin;
use App\Models\UsersAddressBookItem;
use App\Srv\Srv;

class UsersAddressBookItemSrv extends Srv{

    public function list($p){
        $query = UsersAddressBookItem::query();
        if ($p['phone']) {
            $query->where('answer_phone', 'like', "%{$p['phone']}%");
        }
        $query->where('incoming_real_name','!=','');
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }
}