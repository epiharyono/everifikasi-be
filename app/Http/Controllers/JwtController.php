<?php

namespace App\Http\Controllers;

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

use GuzzleHttp\Client as GuzzleHttpClient;

class JwtController extends Controller
{


    static function CheckJWT($token)
    {
          try {
              $jwtParts = explode('.', $token);
              if (empty($header = $jwtParts[0]) || empty($payload = $jwtParts[1]) || empty($jwtParts[2])) {
                  return ['success' => false, 'message' => 'No JWT Auth'];
              }
          } catch (Throwable $e) {
              return ['success' => false, 'message' => 'Provided JWT is invalid.'];
          }

          if (
              !($header = base64_decode($header))
              || !($payload = base64_decode($payload))
          ) {
              return ['success' => false, 'message' => 'Provided JWT can not be decoded from base64.'];
          }

          if (
              empty(($header = json_decode($header, true)))
              || empty(($payload = json_decode($payload, true)))
          ) {
              return ['success' => false, 'message' => 'Provided JWT can not be decoded from JSON.'];
          }

          $tokenParts = explode(".", $token);
          $tokenHeader = base64_decode($tokenParts[0]);
          $tokenPayload = base64_decode($tokenParts[1]);
          $tokenPayload = json_decode($tokenPayload, true);
          $id = $tokenPayload['id'];
          $name = $tokenPayload['name'];
          $nik  = $tokenPayload['nik'];
          $nip  = $tokenPayload['nip'];

          return ['success' => true, 'message' => 'Sukses Get Data', 'data' => $tokenPayload];
    }


    static function GetUser($user_id,$token){
          try {
              $url = config("app.auth_server").'/api/v1/user/'.$user_id;
              $client = new GuzzleHttpClient();
              $apiRequest = $client->request('GET', $url,[
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

    static function SearchUser($req,$token){
          try {
              $url = config("app.auth_server").'/users/search?search='.$req->search;
              $client = new GuzzleHttpClient();
              $apiRequest = $client->request('POST', $url,[
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
