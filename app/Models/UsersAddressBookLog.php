<?php
namespace App\Models;

class UsersAddressBookLog extends BaseAuth{

    protected $fillable = ['user_id', 'phone', 'real_name','type','status'];

    protected  $table = 'users_address_book_log';
}