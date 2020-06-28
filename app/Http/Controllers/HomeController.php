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
    public $a1;

    public function geta1(){
        return $this->a1;
    }

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
        $towns = ['Красноярск' => '100510397251', 'New York' => '100510629862', 'Detroit' => '100532706033'];

        /*$uri = 'https://login.bgoperator.ru/auth?';
        $post = http_build_query([
            'login' => 'nastiy018',
            'pwd' => '.AP_IN!RjDU9NodKDeO37',
        ]);
        $options = [
            'headers' => ['Accept-Encoding' => 'gzip'],
        ];*/

        $data = [
            'ssl' => false,
            'subdomain' => 'export',
            'domain' => 'bgoperator.ru',
            'subdirectory' => 'yandex',
            'values' => []
        ];

        $result = new APIRequest;
        $ddd = $result->APIRequestBuilder($data);
        dump($ddd);




        /*$client = new Client(['cookies' => true]);
        $send = $client->post($uri . $post, $options);
        $jar = $client->getConfig('cookies');
        dump($send);
        dump($jar->toArray());

        dump(is_null($this->a1));
        dump($this->geta1());

        $uri1 = 'http://export.bgoperator.ru/yandex?';
        $post1 = http_build_query([
            'action' => 'hotels',
        ]);
        $options1 = [
            'headers' => ['Accept-Encoding' => 'gzip'],
            'cookies' => $jar,
        ];


        for($i = 0; $i < 5; $i++){
            $send1 = $client->post($uri1 . $post1, $options1);
            $jar1 = $client->getConfig('cookies');
            dump($send1);
            dump($jar1->toArray());
        }


        dump($client);*/
        return view('home', ['towns' => $towns]);
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
