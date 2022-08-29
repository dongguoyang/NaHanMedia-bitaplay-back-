<?php
namespace App\Srv\Admin;

use App\Models\UsersAddressBookLog;
use App\Srv\Srv;

class UsersAddressBookLogSrv extends Srv{

    public function list($p){
        $query = UsersAddressBookLog::query();
        if ($p['user_book_id']) {
            $query->where('user_book_id', '=', $p['user_book_id']);
        }
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }
}