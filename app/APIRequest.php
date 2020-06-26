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
    private array $query /** @var array must ba an array and have a next structure*/;
    private CookieJar $cookie;
    private Client $client;

    /**
     * @param $option
     * @return string
     */
    public function getQuery($option): string
    {
        return $this->query[$option];
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
        $url = $data['ssl'] ? 'https://' : 'http://';
        $url .= $data['subdomain'] . '.' . $data['domain'] . '/' . $data['subdirectory'] . '?';
        $url .= http_build_query($data['values']);
        $this->query[$data['subdomain']] = $url;
        return $this->getQuery($data['subdomain']);
    }

    private function setClient()
    {
        $this->client = new Client(['cookies' => true]);
        return $this->getClient();
    }

    public function setCookie(CookieJar $cookie = null)
    {
        $this->cookie = $cookie ? $cookie : new CookieJar();
    }

//    public function __construct () {
//        $this->now = Auth::user();
//        $this->apiQueryDomain = 'http://export.' . $this->domain . '/';
//        $this->authQueryDomain = 'https://login.' . $this->domain . '/auth?';
//        $this->cookie = DB::table('api_auth')
//            ->where('email', '=', $this->now->email)
//            ->select('a1', 'z1', 'l')
//            ->get();
//    }

    public function APIRequestBuilder(array $query, string $option, $headers = null) {
        $options = [
            'headers' => array_merge(['Accept-Encoding' => 'gzip'], $headers),
            'cookies' => $this->cookie
                ? $this->getCookie()
                : $this->getAuth(),
        ];
        $uri[$option] = array_key_exists($option, $this->query)
            ? $this->getQuery($option)
            : $this->setQuery($query);
        !$this->getClient() ? $this->setClient() : true;
        $request = new Request('post', $uri[$option]);
        $this->APIRequestSender($request, $options, $uri[$option]);
    }

    protected function APIRequestSender(Request $request, array $options, string $uri) {
        try {
            $response = $this->client->send($request, $options);
            $this->getValueFromHeader($response->getHeaders());
        } catch (RequestException $e) {
            $this->APIExceptionHandler($e);
            $this->counter += 1;
        }
        return $this->APIResponseHandler($response);
    }

    protected function APIExceptionHandler($code) {
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

    protected function getAuth() {
        $auth = DB::table('api_auth')
            ->where('email', '=', Auth::user()->mail)
            ->select('login', 'password')
            ->get();
        $auth = $auth->toArray();
        $auth['pwd'] = $auth['password'];
        unset($auth['password']);
        $this->APIRequestBuilder($auth, 'null', 'login');
    }

    protected function APIResponseHandler($response) {
        $contentType = $response->getHeader('Content-Type');
        $checkJSON = 'application/json';
        $checkHTML = 'text/html';
        $this->newZ1 = $response->getCookieByName('z1');
        if (strripos($contentType, $checkJSON)){
            return new APIResponse(\GuzzleHttp\json_decode($response->getBody()));
        }
        elseif (strripos($contentType, $checkHTML)) {
            DB::table('api_auth')
                ->where('email', '=', Auth::user()->mail)
                ->update([
                    'A1' => $response->getCookieByName('A1')->getValue(),
                    'L' => $response->getCookieByName('L')->getValue(),
                ]);
            $this->cookieZ1Changer($response->getCookieByName('Z1')->getValue());
            return true;
        }
        else {
            return 'Мы не знаем, что это такое';
        }
    }

    protected function cookieZ1Changer(string $new) {
        DB::table('api_auth')
            ->where('email', '=', Auth::user()->mail)
            ->update([
                'Z1' => $new,
            ]);
    }

    public static function setValuesFromCookies($headers, $jar) {
        $jar1 = $jar->toArray();
        $oldJar = $jar->toArray();

        dump($oldJar);
        dump($headers);
        foreach ($oldJar as $jarElement) {
            foreach($headers as $header) {
                $arr = explode('; ', $header);
                foreach ($arr as $piece) {
                    $one = explode('=', $piece);
                    if (array_key_exists($one[0], $jarElement)) {
                        if (isSet($one[1])) {
                            $jarElement[$one[0]] = $one[1];
                        }
                    }
                }
            }
        }
    }
}
