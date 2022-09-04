<?php

namespace App\Srv\Api;

use App\Models\User;
use App\Models\UsersAddressBook;
use App\Models\UsersAddressBookItem;
use App\Models\UsersAddressBookLog;
use App\Srv\Srv;
use Illuminate\Support\Facades\DB;

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
        if ($userAddrBookList) {
            $beforeData = array_column($userAddrBookList, 'phone');
            foreach ($userAddrBookList as $k => $v) {
                $beforeDataMap[$v['phone']] = $v['id'];
            }
            foreach ($after as $k => $v) {
                if (!in_array($v['phone'], $beforeData)) {
                    //判断通讯录姓名是否为真实姓名
                    $isRealName = $this->updateRealNameNew($v['phone'], $v['real_name']);
                    $insert['is_real'] = $isRealName['isRealName'] ? 1 : 0;
                    $insert['phone'] = $v['phone'];
                    $insert['real_name'] = $isRealName['realName'];
                    $insert['user_id'] = $user['id'];
                    $insert['type'] = 1;
                    $insert['created_at'] = date("Y-m-d H:i:s", time());
                    $insert['updated_at'] = date("Y-m-d H:i:s", time());
                    $insert['status'] = $isRealName['status'];
                    $insert['user_book_id'] = $isRealName['userBookId'];
                    array_push($insertList, $insert);
                }
            }
        } else {
            foreach ($after as $k => $v) {
                //判断通讯录姓名是否为真实姓名
                $isRealName = $this->updateRealNameNew($v['phone'], $v['real_name']);
                $insert = [
                    'phone' => $v['phone'],
                    'real_name' => $isRealName['realName'],
                    'user_id' => $user['id'],
                    'type' => 1,
                    'created_at' => date("Y-m-d H:i:s", time()),
                    'updated_at' => date("Y-m-d H:i:s", time()),
                    'is_real' => $isRealName['isRealName'] ? 1 : 0,
                    'status' => $isRealName['status'],
                    'user_book_id' => $isRealName['userBookId']
                ];
                array_push($insertList, $insert);
            }
        }
        if ($insertList) {
            UsersAddressBook::insert($insertList);
        }

        return $this->returnData(ERR_SUCCESS, '');
    }


    public function changeRealName($p)
    {
        //判断只能修改一次
        $user = $this->getUser();
        $user = $user->toArray();
        $queryLog = UsersAddressBookLog::query();
        $userLog = $queryLog->where('user_id', '=', $user['id'])->where('phone', '=', $p['phone']);
        if ($userLog->count()) {
            return $this->returnData(ERR_FAILED, '只能修改一次');
        }
        $date = $this->updateRealNameNew($p['phone'], $p['real_name']);
        $userBookQuery = UsersAddressBook::query();
        $userBookList = $userBookQuery->where('phone', '=', $p['phone'])->where('user_id', '=', $user['id']);
        if (!$date['isRealName'] && $date['count'] == 1) {
            $date['isRealName'] = true;
        }
        if ($userBookList->count()) {
            //更新
            $userBookList = $userBookList->first();
            $userBookList->save([
                'user_book_id' => $date['userBookId'],
                'is_real' => $date['isRealName'] ? 1 : 0,
                'real_name' => $date['realName'],
                'company' => $p['company']
            ]);
        } else {
            //新增
            UsersAddressBook::create(
                [
                    'user_id' => $user['id'],
                    'user_book_id' => $date['userBookId'],
                    'is_real' => $date['isRealName'] ? 1 : 0,
                    'real_name' => $p['real_name'],
                    'phone' => $p['phone'],
                    'type' => 2,
                    'status' => $date['status'],
                    'company' => $p['company']
                ]
            );
        }
        //增加用户修改记录
        UsersAddressBookLog::create(
            [
                'user_id' => $user['id'],
                'phone' => $p['phone'],
                'real_name' => $p['real_name'],
                'type' => 2,
                'status' => $date['isRealName'] ? 1 : 2,
            ]
        );
        return $this->returnData(ERR_SUCCESS, '');
    }


    private function updateRealNameNew($phone, $realName)
    {
        $returnData['isRealName'] = true;
        $returnData['realName'] = $realName;
        $returnData['status'] = 1;
        $returnData['is_certification'] = 0; //是否实名认证
        $returnData['userBookId'] = -1;
        $returnData['count'] = 0;
        $addrBookList = UsersAddressBook::where('phone', '=', $phone)->select();
        $returnData['count'] = $addrBookList->count();
        $userQuery = User::with('info');
        //查询手机号是否注册
        $userInfo = $userQuery->where('tel', '=', $phone)->first();
        if ($userInfo) {
            $returnData['status'] = 2;
            $returnData['userBookId'] = ($userInfo->toArray())['id'];
            //查询注册手机号是否实名认证
            if ($userInfo->info) {
                $returnData['realName'] = $userInfo->info->name;
                $returnData['isRealName'] = true;
                $returnData['is_certification'] = 1;
                //更新来电记录
                UsersAddressBookItem::query()->where('incoming_phone', '=', $phone)->update(['incoming_real_name' => $userInfo->info->name]);
                return $returnData;
            }
        }

        $realNameTrue = $realName;
        if ($addrBookList->count() < 1) {
            //数据库中不存在 就是真实姓名
            $returnData['isRealName'] = true;
            $returnData['count'] = 1;
        } else {
            $realNameList = $addrBookList->groupBy('real_name')->select(DB::raw('count(real_name) as num,real_name,created_at'))->orderByDesc('num')->oldest()->get()->toArray();
            foreach ($realNameList as $key => $value) {
                if ($value['real_name'] == $realName) {
                    $realNameList[$key]['num'] += 1;
                    $returnData['count'] = $realNameList[$key]['num'];
                }
            }
            $num = array_column($realNameList, 'num');
            $time = array_column($realNameList, 'created_at');
            array_multisort($num, SORT_DESC, $time, SORT_ASC, $realNameList);
            if ($realNameList[0]['real_name'] == $realName) {
                $returnData['isRealName'] = true;
            } else {
                $returnData['isRealName'] = false;
                $realNameTrue = $realNameList[0]['real_name'];
            }
            //更新来电记录
            UsersAddressBookItem::query()->where('incoming_phone', '=', $phone)->update(['incoming_real_name' => $realNameTrue]);
            if ($realNameTrue != $realName) {
                //增加用户修改记录
                UsersAddressBookLog::create(
                    [
                        'phone' => $phone,
                        'real_name' => $realNameTrue,
                        'type' => 1,
                        'status' => 2,
                    ]
                );
            }
        }
        return $returnData;
    }


    /**
     * 更新手机号真实姓名
     */
    private function updateRealName($phone, $realName)
    {
        $returnData['isRealName'] = true;
        $returnData['realName'] = $realName;
        $returnData['status'] = 1;
        $returnData['is_certification'] = 0; //是否实名认证
        $addrBookList = UsersAddressBook::where('phone', '=', $phone)->select();
        $returnData['count'] = $addrBookList->count();
        $userQuery = User::with('info');
        //查询手机号是否注册
        $userInfo = $userQuery->where('tel', '=', $phone)->first();
        if ($userInfo->count() > 0) {
            $returnData['status'] = 2;
            $returnData['userBookId'] = ($userInfo->toArray())['id'];
            //查询注册手机号是否实名认证
            if ($userInfo->info) {
                $returnData['realName'] = $userInfo->info->name;
                $returnData['isRealName'] = true;
                $returnData['is_certification'] = 1;
                //修改其他姓名为假
                UsersAddressBook::query()->where('phone', '=', $phone)->where('real_name', '!=', $returnData['realName'])->update(['is_real' => 0]);
                UsersAddressBookLog::query()->where('phone', '=', $phone)->where('real_name', '!=', $returnData['realName'])->update(['status' => 0]);
                return $returnData;
            }
        }
        if ($addrBookList->count() == 0) {
            //数据库中不存在 就是真实姓名
            $returnData['isRealName'] = true;
        } else if ($addrBookList->count() == 1) {
            //数据库中指存在一次 姓名为假
            if ($addrBookList->where('real_name', '=', $realName)->count()) {
                $returnData['isRealName'] = true;
            } else {
                $returnData['isRealName'] = false;
            }
        } else {
            //存在多次 姓名重复多的名称为真实姓名
            $realNameList = $addrBookList->groupBy('real_name')->select(DB::raw('count(real_name) as num,real_name'))->orderByDesc('num')->oldest()->get()->toArray();
            //找出重复最多的一个
            if ($realNameList[0]['num'] == 1) {

            }
            if ($realNameList[0] == $realName) {
                //之前的姓名为假
                UsersAddressBook::query()->where('phone', '=', $phone)->where('real_name', '!=', $realName)->update(['is_real' => 0]);
                UsersAddressBook::query()->where('phone', '=', $phone)->where('real_name', '=', $realName)->update(['is_real' => 1]);
                //更新来电记录
                UsersAddressBookItem::query()->where('incoming_phone', '=', $phone)->update(['incoming_real_name' => $realName]);
                UsersAddressBookLog::query()->where('phone', '=', $phone)->where('real_name', '!=', $realName)->update(['status' => 1]);
                UsersAddressBookLog::query()->where('phone', '=', $phone)->where('real_name', '=', $realName)->update(['status' => 2]);
                //记录一条系统变更记录
                //增加用户修改记录
                UsersAddressBookLog::create(
                    [
                        'phone' => $phone,
                        'real_name' => $realName,
                        'type' => 1,
                        'status' => 2,
                    ]
                );
                $returnData['isRealName'] = true;
            } else {
                $returnData['isRealName'] = false;
            }
        }
        return $returnData;
    }

}