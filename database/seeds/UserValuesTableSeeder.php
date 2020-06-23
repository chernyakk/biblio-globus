<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserValuesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_values')->insert(
            [
                [
                    'id' => 1,
                    'name' => 'ОК СОЧИ ПАРК ОТЕЛЬ 3',
                    'entity' => 'hotel',
                ],
                [
                    'id' => 2,
                    'name' => 'GAMMA SIRIUS (бывш. кв. Чистые Пруды) 3',
                    'entity' => 'hotel',
                ],
                [
                    'id' => 3,
                    'name' => 'SIGMA SIRIUS, пансионат (бывш. кв. Александровский сад) 3',
                    'entity' => 'hotel',
                ],
                [
                    'id' => 4,
                    'name' => 'ГОРКИ ГОРОД, апарт-отель 1',
                    'entity' => 'hotel',
                ],
                [
                    'id' => 5,
                    'name' => 'Сочи парк',
                    'entity' => 'excursion',
                ],
                [
                    'id' => 6,
                    'name' => 'Чистые пруды',
                    'entity' => 'excursion',
                ],
                [
                    'id' => 7,
                    'name' => 'Александровский сад',
                    'entity' => 'excursion',
                ],
                [
                    'id' => 8,
                    'name' => 'Красная поляна (Горки город)',
                    'entity' => 'excursion',
                ],
                [
                    'id' => 9,
                    'name' => 'Олимпийский парк',
                    'entity' => 'excursion',
                ],
            ]
        );
    }
}
