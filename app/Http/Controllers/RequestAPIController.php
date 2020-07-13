<?php

namespace App\Http\Controllers;

use App\APIRequest;
use App\ExcelFile;
use Illuminate\Http\Request;
use DateTime;
use Illuminate\Support\Facades\DB;

class RequestAPIController extends Controller {

    public function makeRequest(Request $request){
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

        $date1 = new DateTime($request['date1']);
        $date2 = new DateTime($request['date2']);
        $nowDate = date("d.m.Y", strtotime($request['date1']));
        $diffDate = $date1->diff($date2)->d ? $date1->diff($date2)->d : 1;

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
                'f7' => $diffDate,

            ],
            'hotels' => [],
//            'duration' => [],
        ];

        foreach($hotels as $hotel){
            array_push($data['hotels'], ['F4' => $hotel->api_id]);
        }

        // функционал под неконкретное количество дней, пока нет необходимости
        //        foreach(range($diffDate - 1, $diffDate) as $days){
        //            array_push($data['duration'], ['f7' => $days]);
        //        }

        return $result->APIRequestBuilder($data);
    }

    public function makeExcel(Request $request) {
        $file = new ExcelFile($request->all());
        return $file->setValues();
    }
}
