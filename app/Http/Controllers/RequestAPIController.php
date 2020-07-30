<?php

namespace App\Http\Controllers;

use App\APIRequest;
use App\ExcelFile;
use App\RequestsAPIFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestAPIController extends Controller {

    public static function makeRequest(Request $request){
        $result = new APIRequest();
        $request = $request->all();

        $userValues = DB::table('user_values')
            ->select('name', 'api_id', 'entity')
            ->whereIn('entity', ['hotel', 'adult', 'kid'])
            ->get();
        $hotels = $userValues
            ->where('entity', '=', 'hotel')
            ->all();
        $adult = $userValues
            ->where('entity', '=', 'adult')
            ->first()
            ->api_id;
        $kid = $userValues
            ->where('entity', '=', 'kid')
            ->first()
            ->api_id;

        $people = '';
        for ($i = 0; $i < $request['adults']; $i++){
            $people .= $adult . '.';
        }
        if ($request['kids']){
            for ($i = 0; $i < $request['kids']; $i++) {
                $people .= $kid . '.';
            }
        }

        $nowDate = date("d.m.Y", strtotime($request['date']));
        //пока висит, потом уберём
        $request['days'] = false;

        $data = [
            'scheme' => 'http',
            'host' => 'export.bgoperator.ru',
            'path' => '/partners',
            'query' => [
                'action' => 'price',
                'flt' => '100410000050',
                'flt2' => '100510000863',
                'tid' => '211',
                'id_price' => '-1',
                'data' => $nowDate,
                'd2' => $nowDate,
                'ins' => '0-250000-RUR',
                'p' => substr($people,0, -1),
                'xml' => '11',
                'f7' => $request['diffDate'],
            ],
            'hotels' => [],
            'service' => [
                'date' => strtotime($request['date']),
                'days' => $request['diffDate'],
            ]
        ];

        foreach($hotels as $hotel){
            array_push($data['hotels'], ['F4' => $hotel->api_id]);
        }

        switch ($request['days']){
            case true:
                $factory = new RequestsAPIFactory($data, Auth::user()->email);
                return $factory->getRequests();
                break;
            default:
                return $result->APIRequestBuilder($data, Auth::user()->email);
        }
    }

    public static function makeExcel(Request $request) {
        $file = new ExcelFile($request->all(), Auth::id());
        return $file->setValues();
    }

    public static function getAllDaysPrices($query = null, $email = null) {
        $data = [
            'scheme' => 'http',
            'host' => 'export.bgoperator.ru',
            'path' => '/partners',
            'query' => [
                'action' => 'price',
                'flt' => '100410000050',
                'flt2' => '100510000863',
                'tid' => '211',
                'id_price' => '-1',
                'data' => '08.08.2020',
                'd2' => '08.08.2020',
                'ins' => '0-250000-RUR',
                'p' => '0130619840.0130619840',
                'xml' => '11',
                'f7' => '7',
            ],
            'hotels' => [],
            'service' => [
                'date' => strtotime('08.08.2020'),
                'days' => 7,
            ],
        ];
        $email = 'admin@admin.ru';
        $trying = new RequestsAPIFactory($data, $email);
        dd($trying->getRequests());

    }
}
