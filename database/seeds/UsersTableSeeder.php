<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dt = Carbon::now()->toDateTimeString();

        DB::table('users')->insert(
            [
                'name' => 'Админ',
                'email' => 'admin@admin.ru',
                'password' => bcrypt('81726354'),
                'created_at' => $dt,
                'updated_at' => $dt,
                'api_token' => hash('sha256', Str::random(80)),
            ]
        );
    }
}
