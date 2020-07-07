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
                    'api_id' => 102610084348,
                ],
                [
                    'id' => 2,
                    'name' => 'GAMMA SIRIUS (бывш. кв. Чистые Пруды) 3',
                    'entity' => 'hotel',
                    'api_id' => 102610026611,
                ],
                [
                    'id' => 3,
                    'name' => 'SIGMA SIRIUS, пансионат (бывш. кв. Александровский сад) 3',
                    'entity' => 'hotel',
                    'api_id' => 102610026739,
                ],
                [
                    'id' => 4,
                    'name' => 'ГОРКИ ГОРОД, апарт-отель 1',
                    'entity' => 'hotel',
                    'api_id' => 102616630651,
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
                [
                    'id' => 10,
                    'name' => 'Идентификатор взрослого',
                    'entity' => 'adult',
                    'api_id' => '0130619840'
                ],
                [
                    'id' => 11,
                    'name' => 'Идентификатор ребёнка',
                    'entity' => 'kid',
                    'api_id' => '0140520100'
                ],
            ]
        );
    }
}
