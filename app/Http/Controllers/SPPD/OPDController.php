<?php

namespace App\Http\Controllers\SPPD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Crypt;
use Input;
use View;
use Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


use App\Models\User;
use App\Models\SPPD;
use App\Models\OPD;

use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;

class OPDController extends Controller
{

   static function GetData($req){
        $success = false; $message = 'Otoritas Tidak Diizinkan';
        $super   = 1;
        $token = $req->bearerToken();

        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 401);
        }
        $nip  = $user['data']['nip'];

        // $ha = HALocal::HakAksesUser($nip,2);
        // if(!$ha['lihat']){
        //    return response()->json([
        //        'success' => false,
        //        'message' => $message.' '.$nip,
        //        'ha'  => $ha
        //    ],200);
        // }
        // $super = 0;
        $success = true; $message = 'Sukses Get Data Users';
        $userl  = HALocal::GetTableUser($nip);

        $data  = OPD::get();
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
            'user'  => $userl,
        ], 200);

    }

}
