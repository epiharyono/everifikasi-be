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
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\BadResponseException;

class ApiRequest extends Controller
{

   static function API($url,$body,$external_id){
       $date = Carbon::now()->setTimezone('Asia/Jakarta');
       $date = $date->toIso8601String();
       $client_id = 'sipd';
       $client_id = 'sipd-asisanambas';

       $token = AccessToken::GetAccessToken();
       $sign = AccessToken::sign($client_id,$date);
       $signature = $sign['signature'];

       try {
           $url = config("app.url_bank").'/sipd-api/v2/'.$url;
           $client = new GuzzleHttpClient();
           $apiRequest = $client->request('POST', $url,[
               'headers' => [
                 'Content-Type' => 'application/json',
                 'X-TIMESTAMP' => $date,
                 'X-CLIENT-KEY'  => 'sipd',
                 'X-SIGNATURE' => $signature,
                 'ORIGIN'  => 'http://sipd-ri.co.id',
                 'X-PARTNER-ID'  => '82150823919040624621823174737537',
                 'X-EXTERNAL-ID' => $external_id,
                 'X-IP-ADDRESS'  => '103.76.26.91',
                 'X-DEVICE-ID' => '-',
                 'X-LATITUDE'  => '-',
                 'X-LONGITUDE' => '-',
                 'CHANNEL-ID'  => '-',
                 'Authorization' => 'Bearer ' . $token,
                 'Authorization-Customer'  => 'Bearer '.$token,
               ],
               'verify' => false,
               'body' => json_encode($body)
           ]);

           // return $apiRequest;
           $apiRequest  = $apiRequest->getBody();
           // return $apiRequest;
           $apiRequest = json_decode($apiRequest);
           return $apiRequest;

       } catch (ClientException $e) {
           $message = $e->getMessage();
           $response = $e->getResponse();
           $response = $response->getBody()->getContents();
           $message = json_decode($response);
           return ['success' => false, 'message' => $message, 'code' => $e->getResponse()->getStatusCode()];
       }

    }

    static function APINEW($url,$body,$external_id){
        $date = Carbon::now()->setTimezone('Asia/Jakarta');
        $date = $date->toIso8601String();
        $client_id = 'sipd';
        $client_id = 'sipd-asisanambas';

        $token = AccessToken::GetAccessToken();
        $sign = AccessToken::sign($client_id,$date);
        $signature = $sign['signature'];

        try {
            $url = config("app.url_bank").'/sipd-api/v2/'.$url;
            $client = new GuzzleHttpClient();
            $apiRequest = $client->request('POST', $url,[
                'headers' => [
                  'Content-Type' => 'application/json',
                  'X-TIMESTAMP' => $date,
                  'X-CLIENT-KEY'  => $client_id,
                  'X-SIGNATURE' => $signature,
                  'ORIGIN'  => 'http://sipd-ri.co.id',
                  'X-PARTNER-ID'  => '82150823919040624621823174737537',
                  'X-EXTERNAL-ID' => $external_id,
                  'X-IP-ADDRESS'  => '103.76.26.91',
                  'X-DEVICE-ID' => '-',
                  'X-LATITUDE'  => '-',
                  'X-LONGITUDE' => '-',
                  'CHANNEL-ID'  => '-',
                  'Authorization' => 'Bearer ' . $token,
                  'Authorization-Customer'  => 'Bearer '.$token,
                ],
                'verify' => false,
                'body' => json_encode($body)
            ]);

            $apiRequest  = $apiRequest->getBody();
            $success    = true;
            return [
                'success' => true,
                'data'  => json_decode($apiRequest),
            ];

        } catch (ClientException $e) {
            $message = $e->getMessage();
            $response = $e->getResponse();
            $response = $response->getBody()->getContents();
            $message = json_decode($response);
            return ['success' => false, 'message' => $message, 'code' => $e->getResponse()->getStatusCode()];
        }

     }



}
