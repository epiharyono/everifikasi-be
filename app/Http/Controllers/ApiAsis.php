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

class ApiAsis extends Controller
{
    static function SendMessage($hp,$pesan){
        $from  = self::ReplaceNoHP($hp);
        $client = new GuzzleHttpClient();
        $url    = 'http://103.76.26.91:3000/client/sendMessage/1';
$pesan = str_replace('\n','
',$pesan);

        $apiRequest = $client->request('POST', $url,[
            'headers' => [
              'Content-Type' => 'application/json',
              'Authorization' => 'Bearer uf5f945da1f444e4.f1df22788cd94a3f8947813ffbe63498'
            ],
            'verify' => false,
            'body' => json_encode([
                'recipient_type'  => 'individual',
                'contentType' => 'string',
                'to'  => $hp.'@c.us',
                'chatId'  => $from.'@c.us',
                'type'  => 'text',
                'text'  => [
                  'body'  => $pesan,
                ],
                'content'  => $pesan,
            ])
        ]);
        $content = json_decode($apiRequest->getBody()->getContents());
        return $content;
    }

    static function GetUsers($token,$nip){
        $client = new GuzzleHttpClient();
        $url    = 'http://192.168.100.34/user/find-nip';

        $apiRequest = $client->request('POST', $url,[
            'headers' => [
              'Content-Type' => 'application/json',
              'Authorization' => 'Bearer '.$token,
            ],
            'verify' => false,
            'body' => json_encode([
                'nip'  => $nip,
            ])
        ]);
        $content = json_decode($apiRequest->getBody()->getContents());
        return $content;
    }

    static function GetHtHUsers($nip){
        $client = new GuzzleHttpClient();
        $token  = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9';
        $url    = 'http://192.168.100.34/web/user/find-hth';

        $apiRequest = $client->request('POST', $url,[
            'headers' => [
              'Content-Type' => 'application/json',
              'token' => 'Bearer '.$token,
            ],
            'verify' => false,
            'body' => json_encode([
                'nip'  => $nip,
            ])
        ]);
        $content = json_decode($apiRequest->getBody()->getContents());
        return $content;
    }

    static function ReplaceNoHP($hp){
        if(substr($hp,0,1) == '+'){
            return substr($hp,1,strlen($hp));
        }
        elseif(substr($hp,0,1) == '0'){
            $hps = substr($hp,1,strlen($hp));
            return '62'.$hps;
        }else{
          return $hp;
        }
    }


}
