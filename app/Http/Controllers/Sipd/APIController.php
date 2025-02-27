<?php

namespace App\Http\Controllers\Sipd;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Hash;
use App\User;
use Input;
use Response;
use Auth;
use Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use App\Http\Controllers\Users\DataController as DATA;
use App\Http\Controllers\Users\UserController as Userc;

use GuzzleHttp\Client as GuzzleHttpClient;

class APIController extends Controller
{

    static function SIPD($token,$url,$method){
          try {
              $url    = 'https://service.sipd.kemendagri.go.id'.$url;
              $client = new GuzzleHttpClient();
              $apiRequest = $client->request($method, $url,[
                  'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                  ],
                  'verify' => false,
              ]);
              $content = json_decode($apiRequest->getBody()->getContents());
              return $content;
          } catch (Throwable $e) {
              return ['success' => false, 'message' => 'Provided JWT is invalid.'];
          }
    }

}
