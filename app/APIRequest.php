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
    private $checker;
    private $onDays;
    private $hotels;


    public function __construct($email, bool $onDays = false)
    {
        $this->userMail = $email;
        $this->setClient();
        $this->onDays = $onDays;
        $this->hotels = DB::table('user_values')
            ->select('name', 'api_id')
            ->where('entity', '=', 'hotel')
            ->get();
    }

    private function getQuery(): string
    {
        return $this->query;
    }

    private function getCookie(): CookieJar
    {
        return $this->cookie;
    }

    private function setQuery (array $data) : void
    {
        $url = $data['scheme'] . "://" . $data['host'] . $data['path'] . "?";
        $url .= is_string($data['query']) ? $data['query'] : http_build_query($data['query']);
        foreach ($data['hotels'] as $num => $hotel) {
            $url .= '&F4=' . $hotel['F4'];
        }
        $this->query = $url;
    }

    private function setClient() : void
    {
        $this->client = new Client(['cookies' => true]);
    }

    private function setCookie(CookieJar $cookie) : void
    {
        $this->cookie = $cookie;
    }

    public function APIRequestBuilder($query, $checker = 'summary') {
        $this->checker = $checker;
        $this->setQuery($query);
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

    private function APIRequestSender(Request $request, array $options) {
        try {
            $response = $this->client->send($request, $options);
        } catch (RequestException $e) {
            $this->APIExceptionHandler($e);
        }
        return $this->APIResponseHandler($response);
    }

    private function APIExceptionHandler($code) {
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

    private function getAuth() {
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
    }

    private function APIResponseHandler($response) {
        $contentType = $response->getHeader('Content-Type')[0];
        if (strripos($contentType, 'json')){
            $returnValues = [];
            switch ($this->checker) {
                case 'summary':
                    $counter = 0;
                    foreach ((json_decode($response->getBody(), true)['entries']) as $entry) {
                        $returnValues[] = array(
                            'id_hotel' => $entry['id_hotel'],
                            'hotel' => $this->hotels
                                ->where('api_id', '=', $entry['id_hotel'])->first()->name,
                            'room' => $entry['room'],
                            'quota' => $entry['quota'],
                            'duration' => $entry['duration'],
                            'tour_date' => $entry['tour_date'],
                            'prices' => array(
                                'total' => $entry['prices'][0]['amount'],
                            )
                        );
                        if ($this->onDays) {
                            for ($day = 0; $day < $entry['duration']; $day++){
                                $nowDate = strtotime($entry['tour_date']) + $day * 24 * 3600;
                                $returnValues[$counter]['prices']['separate'][$nowDate] = null;
                            }
                            $counter++;
                        }
                    }
                    unset($counter);
                    break;
                case 'separate':
                    foreach ((json_decode($response->getBody(), true)['entries']) as $entry) {
                        $returnValues[strtotime($entry['tour_date'])][$entry['id_hotel']][] = array(
                                'room' => $entry['room'],
                                'price' => $entry['prices'][0]['amount'],
                        );
                    }
                    break;
            }
            return $returnValues;

        }
        else {
            return $response->getHeaders();
        }
    }
}
