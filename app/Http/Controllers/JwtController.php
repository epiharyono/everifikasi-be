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
          return ['success' => true, 'message' => 'Sukses Get Data', 'data'=>$tokenPayload ];
    }
}
