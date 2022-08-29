<?php
namespace App\Srv\Admin;

use App\Models\UsersAddressBook;
use App\Srv\Srv;

class UsersAddressBookSrv extends Srv{

    public function realNameList($p)
    {
        $query = UsersAddressBook::with('getUserInfo','getUserBookInfo');
        if ($p['phone']) {
            $query->where('phone', 'like', "%{$p['phone']}%");
        }
        if($p['user_id']){
            $query->where('user_id', '=', $p['user_id']);
        }
        $query->where('real_name','!=','');
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }
}