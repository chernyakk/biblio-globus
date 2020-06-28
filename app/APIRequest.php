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
    public string $query/** @var string must be an array and have a next structure*/;
    public CookieJar $cookie;
    public Client $client;

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
        ($this->cookie == $cookie) ? $this->$cookie = $cookie : new CookieJar();
    }
//        $this->apiQueryDomain = 'http://export.' . $this->domain . '/';

    public function APIRequestBuilder(array $query = null, $headers = []) {
        $this->client = new Client(['cookies' => true]);
        $this->query = $this->setQuery($query);
        $this->cookie = $this->getAuth();
        $options = [
            'headers' => array_merge(['Accept-Encoding' => 'gzip'], $headers),
            'cookies' => $this->getCookie(),
        ];
        if ($this)
        $request = new Request('post', $query);
        $this->APIRequestSender($request, $options);
    }

    public function APIRequestSender(Request $request, array $options) {
        try {
            $response = $this->client->send($request, $options);

        } catch (RequestException $e) {
            $this->APIExceptionHandler($e);
            $this->counter += 1;
        }
        return $this->APIResponseHandler($response);
    }

    public function APIExceptionHandler($code) {
        $status = $code->getResponse()->getStatusCode();
        switch ($status) {
            case 401:
                if ($this->counter < 10) {
                    $this->getAuth();
                } else {
                    redirect('home');
                }
                break;
            case 404:
                redirect('404');
                break;
            case preg_match('5[0-9]{2}', $status):
                redirect('500');
                break;
            default:
                break;
        }
    }

    public function getAuth() {
        $auth = DB::table('api_auth')
            ->where('email', '=', 'admin@admin.ru')
            ->select('username', 'password')
            ->get();
        //dump($auth[0]);
        $auth->flatten();
        dump($auth);
        dump($auth->password);
        dump($auth->username);
        $auth[0]->pwd = $auth['password'];
        //unset($auth['password']);
        $auth[0]->login = $auth['username'];
        //unset($auth['username']);

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
        $this->APIRequestBuilder();
    }

    public function APIResponseHandler($response) {
        $contentType = $response->getHeader('Content-Type');
        $checkJSON = 'application/json';
        if (strripos($contentType, $checkJSON)){
            return \GuzzleHttp\json_decode($response->getBody());
            //new APIResponse->handler(
        }
        else {
            return 'Мы не знаем, что это такое';
        }
    }
}
