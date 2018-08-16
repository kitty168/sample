<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        Model::unguard();

        //指定调用用户数据填充文件
        $this->call(UsersTableSeeder::class);
        //指定调用微博数据填充文件
        $this->call(StatusesTableSeeder::class);

        Model::reguard();
    }
}
