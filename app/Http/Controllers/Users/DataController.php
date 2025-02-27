<?php

namespace App\Http\Controllers\Users;

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

use App\Http\Controllers\JwtController as JWT;

class DataController extends Controller
{

    static function Testing($req) {
        $token = $req->bearerToken();
        $user = '';
        $user  = JWT::CheckJWT($token);

        if(!$user['success']){
            return response()->json([
                'success' => $user['success'],
                'message' => $user['message'],
            ], 401);
        }


        return response()->json([
            'success' => $user['success'],
            'message' => 'Gagal Request Data ...',
            'token' => $token,
            'user_id'  => $user['data']['id'],
            'user'  => $user,
        ], 200);

    }
}
