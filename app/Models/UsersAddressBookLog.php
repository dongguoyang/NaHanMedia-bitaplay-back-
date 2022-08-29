<?php
namespace App\Models;

class UsersAddressBookLog extends BaseAuth{

    protected $fillable = ['user_book_id', 'type', 'status'];

    protected  $table = 'users_address_book_log';
}