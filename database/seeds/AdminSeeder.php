<?php

use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Admin::create([
            'username'=>'admin',
            'password'=>'123456',
            'nickname' => '超级管理员',
            'avatar' => 'https://newknockdoor.oss-cn-chengdu.aliyuncs.com/portrait/202110/9be90bb7f7070aa24fa2a4c8f15f1840.jpg',
            'token'=>md5('admin'),
        ]);
    }
}
