<?php

namespace App\Srv;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class Srv
{
    public function returnData($code = ERR_SUCCESS, $msg = '', $data = '')
    {
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];
    }

    public function pageList($data)
    {
        if ($data instanceof LengthAwarePaginator) {
            $data = $data->toArray();
            return ['list' => $data['data'], 'total' => $data['total']];
        }
        return ['list' => $data['data'], 'total' => $data['total']];
    }

    public function getUser()
    {
        return Auth::guard('api')->user();
    }

    public function getAdmin()
    {
        return Auth::guard('admin')->user();
    }

    public function getProvider()
    {
        return Auth::guard('provider')->user();
    }


    public function encrypt($data, $secret)
    {
//        $data = openssl_encrypt($data, 'AES-128-ECB', $secret, OPENSSL_RAW_DATA);
//        return base64_encode($data);
        return $data;
    }

    public function decrypt($data, $secret)
    {
//        $data = openssl_decrypt(base64_decode($data), 'AES-128-ECB', $secret, OPENSSL_RAW_DATA);
//        return $data;
        return $data;
    }

}

