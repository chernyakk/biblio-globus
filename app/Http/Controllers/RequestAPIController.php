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
                return self::getAllDaysPrices($data);
            default:
                $result = new APIRequest(Auth::user()->email);
                return $result->APIRequestBuilder($data);
        }
    }

    public static function makeExcel(Request $request) {
        $file = new ExcelFile($request->all());
        return $file->setValues();
    }

    public static function getAllDaysPrices($query = null, $email = 'admin@admin.ru') {
        $trying = new RequestsAPIFactory($query, $email);
        $requests = $trying->getRequests();
        $responses = [];
        $client = new APIRequest($email, true);

        foreach ($requests as $request) {
            switch ($request['type']) {
                case 'summary':
                    $responses['summary'] = $client->APIRequestBuilder($request, $request['type']);
                    break;
                default:
                    $response = $client->APIRequestBuilder($request, $request['type']);
                    $responses['separate'][key($response)] = array_values($response)[0];
                    break;
            }
        }
        return self::daysHandler($responses);
    }

    public static function daysHandler($days)
    {
        $targetArray = [];
        for($variant = 0; $variant < count($days['summary']); $variant++){
            $now = $days['summary'][$variant];
            foreach($now['prices']['separate'] as $date => $price){
                $nowPool = array_filter(
                    $days['separate'][$date][$now['id_hotel']],
                    function ($entry) use ($now) {
                        return $entry['room'] == $now['room'];
                });
                unset($now['prices']['separate'][$date]);
                $now['prices']['separate'][date('d.m.Y', $date)] = $nowPool
                    ? current($nowPool)['price']
                    : 'Невозможно получить цену текущего дня';
            }
            $targetArray[$variant] = $now;
        }
        unset($days);
        return $targetArray;
    }
}
