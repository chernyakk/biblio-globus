<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;


class APIRequest extends Model
{
    private $query;
    private $cookie;
    private $client;


    public function getQuery(): string
    {
        return $this->query;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getCookie(): CookieJar
    {
        return $this->cookie;
    }

    public function setQuery (array $data)
    {
        $url = $data['scheme'] . "://" . $data['host'] . $data['path'] . "?";
        $url .= is_string($data['query']) ? $data['query'] : http_build_query($data['query']);
        foreach ($data['hotels'] as $num => $hotel) {
            $url .= '&F4=' . $hotel['F4'];
        }
//        foreach ($data['duration'] as $num => $date) {
//            $url .= '&f7=' . $date['f7'];
//        }
        $this->query = $url;
    }

    public function setClient()
    {
        $this->client = new Client(['cookies' => true]);
    }

    public function setCookie(CookieJar $cookie = null)
    {
        $this->cookie = $cookie;
    }

    public function APIRequestBuilder(array $query = []) {
        if (!isset($this->client)) $this->setClient();
        if (!isset($this->query)) $this->setQuery($query);
        if (!isset($this->cookie))  $this->getAuth();
        $options = [
            'headers' => [
                'Accept-Encoding' => 'gzip',
                'Content-Type' => 'charset=utf-8'
                ],
            'cookies' => $this->getCookie(),
        ];
        $request = new Request('post', $this->getQuery());
        return $this->APIRequestSender($request, $options);
    }

    public function APIRequestSender(Request $request, array $options) {
        try {
            $response = $this->client->send($request, $options);
        } catch (RequestException $e) {
            $this->APIExceptionHandler($e);
        }
        return $this->APIResponseHandler($response);
    }

    public function APIExceptionHandler($code) {
        $status = $code->getResponse()->getStatusCode();
        switch ($status) {
            case 401:
                $this->getAuth();
                break;
            case 404:
                abort('404');
                break;
            case preg_match('5[0-9]{2}', $status):
                abort('500');
                break;
            default:
                break;
        }
    }

    public function getAuth() {
        $authData = DB::table('api_auth')
            ->where('email', '=', 'admin@admin.ru')
            ->select('username', 'password')
            ->get();
        $auth = [
            'login' => $authData[0]->username,
            'pwd' => $authData[0]->password,
            ];

        $uri = 'https://login.bgoperator.ru/auth?' . http_build_query($auth);
        $request = new Request('post', $uri);
        $options = [
            'headers' => (['Accept-Encoding' => 'gzip']),
        ];
        try {
            $this->client->send($request, $options);
            $this->setCookie($this->client->getConfig('cookies'));
        } catch (RequestException $e) {
            $this->APIExceptionHandler($e);
        }
        return $this->APIRequestBuilder();
    }

    public function APIResponseHandler($response) {
        $contentType = $response->getHeader('Content-Type')[0];
        if (strripos($contentType, 'json')){
            return (json_decode($response->getBody())->entries);
        }
        elseif (strripos($contentType, 'xml')){
            return json_decode($response->getBody()->getContents());
        }
        else {
            return $response->getHeaders();
        }
    }
}
