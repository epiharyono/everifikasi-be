<?php

namespace App\Http\Controllers\BANK;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Crypt;
use Input;
use View;
use Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use App\Http\Controllers\JwtController as JWT;
use App\Models\Ta_Transaksi as TRANSAKSI;
use App\Models\SPPD_CETAK;
use App\Models\SPPD;

use GuzzleHttp\Client as GuzzleHttpClient;

class BRKSController extends Controller
{

   public function GetToken(Request $req){
        $success = false; $message = 'Otoritas Tidak Diizinkan';
        $super   = 1;
        $validator = Validator::make($req->all(), [
            'username'     => 'required',
            'password'  => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //get credentials from request
        $credentials = $req->only('username', 'password');

        if($req->username != "admin" || $req->password != "sipdAnambas321#"){
            return response()->json([
                'status' => 401,
                'message' => 'Login Gagal',
                'payload'  => $req->all(),
            ], 401);
        }

        // Buat payload secara manual
        $exp  = now()->addHour()->timestamp;
        $payload = JWTFactory::customClaims([
            'sub' => 1, // ID user (atau informasi lain yang Anda inginkan)
            'name'  => 'Admin BRKS',
            'pang'  => 'pong',
            'iat' => now()->timestamp, // Waktu pembuatan token
            'exp' => $exp, // Waktu expired (misalnya 1 jam)
            'iss' => 'https://api-asis.anambaskab.go.id',
        ]);
        $payload = $payload->make();
        $token = JWTAuth::encode($payload);

        return response()->json([
            'status' => 200,
            'message' => 'Login Berhasil',
            'token' => $token->get(),
            'name'  => 'Token',
            'type' => 'Bearer',
            'payload'  => $req->all(),
        ], 200);

    }

    public function BankCallback(Request $req){
        $code  = "003";
        $message = "Callback Gagal";
        $code_resp = 200;
        $token = $req->header('token');
        $token = str_replace('Bearer ', '', $token);
        if($token == '' || $token == 'null'){
           return response()->json(['code' => '401','message' => 'invalid or expired jwt'], 401);
        }

        $user  = JWT::CheckJWTBrks($token);
        if(!$user['success']){
           return response()->json(['code' => ''.$code,'message' => $message], 401);
        }

        $tx_overbook_id  = $req->tx_additional_data['tx_overbook_id'];
        $no_sp2d         = $req->tx_additional_data['no_sp2d'];
        $cetak  = SPPD_CETAK::where('tx_overbook_id',$tx_overbook_id)->where('nomor_sp_2_d',$no_sp2d)->first();
        if($cetak){
            $data = $req->data;
            foreach($data as $dat){
                $tx_partner_id = $dat['tx_partner_id'];
                $status_code = $dat['status']['code'];
                $status_message = $dat['status']['message'];

                TRANSAKSI::where('tx_partner_id',$tx_partner_id)->update([
                    'status_code' => $status_code,
                    'status_message'  => $status_message,
                ]);
            }
            $message = 'Callback berhasil';
            $code  = "003";
        }else{
            $code = 401;
            $code_resp = 404;
            $message = "Data OB Tidak Ditemukan";
        }
        return response()->json([
            'code' => ''.$code,
            'message' => $message,
            'status'  => $code_resp,
        ], $code_resp);
    }

}
