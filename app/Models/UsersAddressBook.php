<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;

class UsersAddressBook extends BaseAuth{

    protected $fillable = ['user_id', 'user_book_id', 'nickname','real_name','phone', 'company', 'type', 'status'];

    protected $table = 'users_address_book';


    public function getUserInfo(){
        return $this->hasOne(\App\Models\User::class,'id','user_id');
    }

    public function getUserBookInfo(){
        return $this->hasOne(\App\Models\User::class,'id','user_book_id');
    }

    public function getBookItemList(){
        return $this->hasMany(UsersAddressBookItem::class,'user_id','user_id');
    }

    public function  getBookLogList(){
        return $this->hasMany(UsersAddressBookLog::class,'user_book_id','id');
    }



}
