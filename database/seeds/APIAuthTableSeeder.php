<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class APIAuthTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('api_auth')->insert(
            [
                'username' => 'nastiy018',
                'password' => '.AP_IN!RjDU9NodKDeO37',
                'email' => 'admin@admin.ru'
            ]
        );
    }
}
