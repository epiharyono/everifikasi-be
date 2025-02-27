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

use GuzzleHttp\Client as GuzzleHttpClient;

class AccountInquery extends Controller
{

   static function GetAccessToken(){
        $success = false; $message = 'Otoritas Tidak Diizinkan';
        $super   = 1;

        try {
            $url = config("app.url_bank").'/local/access-token';
            $client = new GuzzleHttpClient();
            $apiRequest = $client->request('POST', $url,[
                'headers' => [
                  'Content-Type' => 'application/json',
                  'X-TIMESTAMP' => '2020-01-01T00:00:00+07:00',
                  'X-CLIENT-KEY'  => ': 962489e9-de5d-4eb7-92a4-b07d44d64bf4',
                  'X-SIGNATURE' => '07abc7c30d245c0ecce3ef6c2a9ac76cd9ffaf6d0d090773b429c2b97437dc72047f46d9890abb2d6d8af75',
                  // 'Authorization' => 'Bearer ' . $token
                ],
                'verify' => false,
            ]);

            $content = json_decode($apiRequest->getBody()->getContents());
            return $content;
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data'  => $content,
            ], 200);

        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Provided JWT is invalid.'];
        }

    }

}
