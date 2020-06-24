<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RequestAPIController extends Controller
{
    private $cookie;
    private $request;
    private $now;
    private $apiQueryDomain;
    private $authQueryDomain;
    private $hotelEntity = 'hotel';
    private $domain = 'bgoperator.ru';
    private $newZ1;
    private $counter = 0;

    public function __construct ($api) {
        $this->request = $api['request'];
        $this->now = Auth::user();
        $this->apiQueryDomain = 'http://export.' . $this->domain . '/';
        $this->authQueryDomain = 'https://login.' . $this->domain . '/';
        $this->cookie = DB::table('api_auth')
            ->where('email', '=', $this->now->email)
            ->select('a1', 'z1', 'l')
            ->get();
    }

    public function APIRequestBuilder($url, $cookies = null, $headers = null, $options = null)
    {
        $baseURI = (array_key_exists('auth', $options))
            ? $this->authQueryDomain
            : $this->apiQueryDomain;
        $query = http_build_query($url);
        $jar = ($cookies == 'user')
            ? CookieJar::fromArray($this->cookie->toArray(), $this->domain)
            : $cookies != null ? $cookies : null;
        $options = [
            'headers' => array_merge(['Accept-Encoding' => 'gzip'], $headers),
            'cookies' => $jar,
        ];
        $futureRequest = new Request('post', $query);
        $this->APIRequestSender($query, $futureRequest, $options);
    }

    public function APIRequestSender($baseURI, $futureRequest, $options)
    {
        $request = new Client(['base_uri' => $baseURI]);
        try {
            $response = $request->send($futureRequest, $options);
        } catch (GuzzleException $e) {
            $this->APIExceptionHandler($e);
        }
        return $this->APIResponseHandler($response);
    }

    protected function APIExceptionHandler($code){
        switch ($code->getStatusCode()) {
            case 200:
                $this->APIresponseHandler($code);
                break;
            case 401:
                if ($this->counter < 10) {
                    $this->getNewAuthParameters();
                } else {
                    return false;
                }
                break;
            case 406:
                if ($this->counter < 10) {
                    return true;
//                    $this->apiRequester('', '', ['Accept-Encoding' => 'gzip']);
                } else {
                    return false;
                }
        }
    }

    protected function getNewAuthParameters(){
        $auth = DB::table('api_auth')
            ->where('email', '=', Auth::user()->mail)
            ->select('login', 'password')
            ->get();
        $auth = $auth->toArray();
        $auth['pwd'] = $auth['password'];
        unset($auth['password']);
        $this->apiRequester($auth, 'null', '', ['auth']);
    }

    protected function APIResponseHandler($response) {
        $contentType = $response->getHeader('Content-Type');
        $checkJSON = 'application/json';
        $checkHTML = 'text/html';
        if (strripos($contentType, $checkJSON)){
            return true;
        }
        elseif (strripos($contentType, $checkHTML)){
            return true;
        }
        else {
            return 'Мы не знаем, что это такое';
        }
    }
}
