<?php

namespace App\Srv\Admin;

use App\Models\UsersAddressBook;
use App\Models\UsersAddressBookItem;
use App\Srv\Srv;

class UsersAddressBookItemSrv extends Srv
{

    public function list($p)
    {
        $query = UsersAddressBookItem::query();
        if ($p['phone']) {
            $query->where('answer_phone', 'like', "%{$p['phone']}%");
        }
        $query->where('incoming_real_name', '!=', '');
        $list = $query->orderByDesc('id')->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }

    public function incomingPhone($p)
    {
        $user = $this->getUser();
        $user = $user->toArray();
        //1.通过手机号查询后台通讯录
        $query = UsersAddressBook::query();
        $bookList = $query->where('phone', '=', $p['phone'])
            ->where('user_id', '!=', $user['id'])
            ->where('is_real', '=', '1')
            ->first();
        return $this->returnData(ERR_SUCCESS, '', $bookList);
    }

    public function saveIncomingLog($p)
    {
        $user = $this->getUser();
        $user = $user->toArray();
        UsersAddressBookItem::create(
            [
                'user_id' => $user['id'],
                'incoming_user_id' => $p['incoming_user_id'],
                'incoming_phone'=>$p['phone'],
                'incoming_real_name'=>$p['real_name'],
                'incoming_company'=>$p['company'],
                'incoming_time'=>$p['incoming_time']
            ]
        );
        return $this->returnData(ERR_SUCCESS, '');
    }

    public function incomingLogList($p){
        $user = $this->getUser();
        $user = $user->toArray();
        $query= UsersAddressBookItem::query();
        $query->where('user_id','=',$user['user_id']);
        $list = $query->orderByDesc('id')->paginate(20,'','',$p['page']);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($list));
    }
}