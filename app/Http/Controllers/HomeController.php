<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

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
        $towns = ['Красноярск' => '100510397251', 'New York' => '100510629862', 'Detroit' => '100532706033'];
        return view('home', ['towns' => $towns]);
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
