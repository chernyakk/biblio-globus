<?php

namespace App;

use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;


class APIRequest
{
    private $query;
    private $cookie;
    private $client;
    private $userMail;


    public function getQuery(): string
    {
        return $this->query;
    }

    public function getCookie(): CookieJar
    {
        return $this->cookie;
    }

    public function setQuery (array $data) : void
    {
        $url = $data['scheme'] . "://" . $data['host'] . $data['path'] . "?";
        $url .= is_string($data['query']) ? $data['query'] : http_build_query($data['query']);
        foreach ($data['hotels'] as $num => $hotel) {
            $url .= '&F4=' . $hotel['F4'];
        }
        $this->query = $url;
    }

    public function setClient() : void
    {
        $this->client = new Client(['cookies' => true]);
    }

    public function setCookie(CookieJar $cookie) : void
    {
        $this->cookie = $cookie;
    }

    public function APIRequestBuilder(array $query = [], $mail = 'admin@admin.ru') {
        if (!isset($this->userId)) $this->userMail = $mail;
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
            ->where('email', '=', $this->userMail)
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
            $returnValues = [];
            foreach ((json_decode($response->getBody(), true)['entries']) as $entry) {
                $returnValues[] = array(
                    'id_hotel' => $entry['id_hotel'],
                    'room' => $entry['room'],
                    'quota' => $entry['quota'],
                    'duration' => $entry['duration'],
                    'prices' => array(
                        'total' => $entry['prices'][0]['amount'],
                        'separate' => [],
                    )
                );
            }
            return $returnValues;
        }
        else {
            return $response->getHeaders();
        }
    }
}
