<?php
namespace App\Models;

class UsersAddressBookItem extends BaseAuth{


    protected $fillable = ['user_id', 'incoming_user_id', 'answer_nickname','answer_phone','incoming_phone', 'incoming_real_name', 'incoming_company', 'incoming_time','incoming_nickname'];


}
