<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Srv\Api\UsersAddressBookSrv;
use Illuminate\Http\Request;

class UsersAddressBookController extends Controller{

    public function uploadAddressBook(Request $request,UsersAddressBookSrv $srv)
    {
        $p = $request->all();

        return $this->responseDirect($srv->uploadAddressBook($p));
    }
}