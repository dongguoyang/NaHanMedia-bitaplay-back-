<?php

namespace App\Srv\Api;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\AddShortUrlResponseBody\data;
use App\Models\UsersAddressBook;
use App\Srv\Srv;
use function Sodium\add;

class UsersAddressBookSrv extends Srv
{

    public function uploadAddressBook($p)
    {
        //查询用户通讯录存在数据
        $user = $this->getUser();
        $user = $user->toArray();
        $query = UsersAddressBook::query();
        $userAddrBookList = $query->where('user_id', '=', $user['id'])->select()->get()->toArray();
        $after = $p['users_address_book_list'];
        $insertList = [];
        $updateList = [];
        if ($userAddrBookList) {
            $beforeData = array_column($userAddrBookList, 'phone');
            foreach ($userAddrBookList as $k => $v) {
                $beforeDataMap[$v['phone']] = $v['id'];
            }
            foreach ($after as $k => $v) {
                if (in_array($v['phone'], $beforeData)) {
                    $update = [
                        'phone' => $v['phone'],
                        'real_name' => $v['real_name'],
                        'user_id' => $user['id'],
                        'id' => $beforeDataMap[$v['phone']],
                        'type' => 1,
                        'created_at'=>date("Y-m-d H:i:s",time()),
                        'updated_at'=>date("Y-m-d H:i:s",time()),

                    ];
                    array_push($updateList, $update);
                } else {
                    $insert = [
                        'phone' => $v['phone'],
                        'real_name' => $v['real_name'],
                        'user_id' => $user['id'],
                        'type' => 1,
                        'created_at'=>date("Y-m-d H:i:s",time()),
                        'updated_at'=>date("Y-m-d H:i:s",time()),
                    ];
                    array_push($insertList, $insert);
                }
            }
        } else {
            foreach ($after as $k => $v) {
                $insert = [
                    'phone' => $v['phone'],
                    'real_name' => $v['real_name'],
                    'user_id' => $user['id'],
                    'type' => 1,
                    'created_at'=>date("Y-m-d H:i:s",time()),
                    'updated_at'=>date("Y-m-d H:i:s",time()),
                ];
                array_push($insertList, $insert);
            }
        }
        if ($insertList) {
            UsersAddressBook::insert($insertList);
        }
        if ($updateList) {
            (new UsersAddressBook())->updateBatch($updateList);
        }
    }
}