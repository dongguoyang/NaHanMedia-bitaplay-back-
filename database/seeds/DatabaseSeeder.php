<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AppCategorySeeder::class);
        $this->call(AppGradeSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(AndroidShopSeeder::class);
        $this->call(SystemSeeder::class);
        $this->call(PlatProviderSeeder::class);
    }

}
