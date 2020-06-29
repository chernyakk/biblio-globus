<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;


class APIRequest extends Model
{
    private $query/** @var string must be an array and have a next structure*/;
    private $cookie;
    private $client;

    /**
     * @param $option
     * @return string
     */
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

    /**
     * setQuery is a method, which make HTTP query from array
     * array construction must include:
     * ssl = boolean, usually false;
     * subdomain = in this project ordinary "export";
     * domain = in this project "bgoperator.ru";
     * subdirectory = "yandex"
     * @param array $data
     * @return string
     */

    public function setQuery (array $data)
    {
        $url = $data['ssl'] ? 'https://' : 'http://';
        $url .= $data['subdomain'] . '.' . $data['domain'] . '/' . $data['subdirectory'] . '?';
        $url .= http_build_query($data['values']);
        $this->query = $url;
        return $this->getQuery();
    }

    public function setClient()
    {
        $this->client = new Client(['cookies' => true]);
        return $this->getClient();
    }

    public function setCookie(CookieJar $cookie = null)
    {
        $this->cookie = $cookie;
        return $this->getCookie();
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
        $checkJSON = 'json';
        $checkXML = 'xml';
        if (strripos($contentType, $checkJSON)){
            return (json_decode($response->getBody()));
//            return $response->getBody();
        }
        elseif (strripos($contentType, $checkXML)){
            return $response->getBody();
        }
        else {
            return 'Мы не знаем, что это такое';
        }
    }
}
