<?php

namespace App\Http\Controllers;

use App\APIRequest;
use Illuminate\Http\Request;

class RequestAPIController extends Controller {
    public static function makeRequest(Request $request){
        $result = new APIRequest();
        $array = $request->all();
        return $result->APIRequestBuilder($array);
    }
}
