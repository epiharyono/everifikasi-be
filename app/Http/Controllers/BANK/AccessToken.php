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
use Carbon\Carbon;

use GuzzleHttp\Client as GuzzleHttpClient;

class AccessToken extends Controller
{

   static function GetAccessToken(){
        $success = false; $message = 'Otoritas Tidak Diizinkan';
        $super   = 1;
        $date = Carbon::now()->setTimezone('Asia/Jakarta');
        $date = $date->toIso8601String();
        $client_id = 'sipd-asisanambas';
        // $client_id = 'sipd';


        $sign = self::sign($client_id,$date);
        $signature = $sign['signature'];

        // return $signature;
        try {
            $url = config("app.url_bank").'/local/access-token';
            $url = config("app.url_bank").'/sipd-api/v2/access-token';
            $client = new GuzzleHttpClient();
            $apiRequest = $client->request('POST', $url,[
                'headers' => [
                  'Content-Type' => 'application/json',
                  'X-TIMESTAMP' => $date,
                  'X-CLIENT-KEY'  => $client_id,
                  'X-SIGNATURE' => $signature,
                  // 'Authorization' => 'Bearer ' . $token
                ],
                'verify' => false,
                'body' => json_encode([
                    "grantType" => "client_credentials",
                    "additionalInfo" => "{}"
                ])
            ]);

            $apiasis  = $apiRequest->getBody();
            $apiRequest = json_decode($apiasis);
            return $apiRequest->accessToken;

        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Provided JWT is invalid.'];
        }

    }

    static function sign($client_id,$date)
    {
        // Data yang akan ditandatangani
        // openssl genpkey -algorithm RSA -out private_key.pem -pkeyopt rsa_keygen_bits:2048

        // $data = $request->input('data', 'This is the message');
        $stringToSign = $client_id.'|'.$date;
        // Baca kunci privat dari file (pastikan file private_key.pem ada di storage)
        $privateKey = file_get_contents(storage_path('keys/private_key.pem'));

        // Hash data menggunakan SHA-256
        // $hash = hash('SHA256', $data, true);

        // Generate signature menggunakan RSA dan kunci privat
        $signature = null;
        // $signature = '';
        // openssl_sign($hash, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        // Konversi tanda tangan menjadi format base64
        $signatureBase64 = base64_encode($signature);

        // Kirim response dengan X-Signature di header
        return [
          'success'  => true,
          'signature' => $signatureBase64,
        ];
    }


}
