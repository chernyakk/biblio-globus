<?php

namespace App\Http\Controllers;


use App\APIRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\RequestAPIController as ForRequest;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        $hotels = DB::table('user_values')
            ->select('name', 'api_id')
            ->where('entity', '=', 'hotel')
            ->get();

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
                'data' => '13.07.2020',
                'd2' => '17.07.2020',
//                'f7' => '1',
                'f3' => '',
                'f8' => '',
                'ins' => '0-250000-RUR',
                'p' => '0130619840.0130619840',
                'xml' => '11',
            ],
            'hotels' => []
//            'query' => 'action=price&tid=211&idt=&flt2=100510000863&id_price=-1&data=13.07.2020&d2=17.07.2020&f7=1&f3=&f8=&ho=0&F4=102610026611&F4=102610026739&F4=102610084348&F4=102616630651&ins=0-250000-RUR&flt=100410000050&p=0130619840.0130619840',
        ];


        foreach($hotels as $hotel){
//            dd($hotel);
            array_push($data['hotels'], ['F4' => $hotel->api_id]);
        }

//        dd(http_build_query($data));

        $data1 = [
            'scheme' => 'http',
            'host' => 'export.bgoperator.ru',
            'path' => '/yandex',
            'query' => [
                'action' => 'hotels',
                'id' => $hotels[0]->api_id,
            ]
        ];

        $result = new APIRequest();
        dd($result->APIRequestBuilder($data));
    }

    public function spa()
    {
        return view('spa');
    }

    public function json(Request $request)
    {
        $GLOBALS['request2'] = $request->all();
        $list = new Client();
        $header = ['headers' => [
            'Accept-Encoding' => 'gzip'
        ]];
        $request1 = $list->get('http://export.bgoperator.ru/auto/jsonResorts.json', $header);
        $request1 = \GuzzleHttp\json_decode($request1->getBody());
        $cities = array_filter($request1, function ($country) {
            return ($country->id == $GLOBALS['request2']['cities']);
        });
        return view('json', ['cities' => $cities]);
    }
}
