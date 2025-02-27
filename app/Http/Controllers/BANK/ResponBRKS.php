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
use App\Models\Ta_Transaksi;

use GuzzleHttp\Client as GuzzleHttpClient;

class ResponBRKS extends Controller
{

   static function AccessToken($req){
        $success = false; $message = 'Otoritas Tidak Diizinkan';
        $super   = 1;

        return response()->json([
            'responseCode' => 290,
            'responseMessage' => $message,
            'accessToken' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiJiZjFmM2Q3ZS1kOTA3LTRkOWItODJlNC02Y2IxZ',
            'tokenType' => 'Bearer',
            'expiresIn' => 3600,
            'data'  => $req->all(),
        ], 200);

    }

    static function ValidasiRekening($req){
         // fungsi ini hanya digunakan sementara
         $success = true; $message = 'Data Berhasil Divalidasi';
         $sync_rekanan = false;
         $trans  = Ta_Transaksi::where('id',$req->id)->first();
         $trans->cek_rekanan();
         if(!$trans->rekanan['success']){
              $success = false;
              $message = $trans->rekanan['message'];
              $sync_rekanan = true;
         }

         $transaksi  = Ta_Transaksi::where('id_spm',$trans->id_spm)->get();
         return response()->json([
             'success' => $success,
             'message' => $message,
             // 'rekanan' => $rekanan,
             'trans' => $trans,
             'transaksi' => $transaksi,
             'sync_rekanan' => $sync_rekanan,
             'data'  => $req->all(),
         ], 200);
     }

     static function ValidasiPajak($req,$id){
         $id_spm = 0;
         $pajak  = DB::table('ta_spm_pajak_potongan')->where('id',$req->id)->first();
         if($pajak){
             DB::table('ta_spm_pajak_potongan')->where('is_valid','!=',1)->where('id',$req->id)->update([
                 'is_valid'  => 1,
                 'status_message'  => 'Kasda Online',
                 'status_code'  => '000',
             ]);
             $id_spm  = $pajak->id_spm;
         }

         $potongan = DB::table('ta_spm_pajak_potongan')->where('id_spm',$id_spm)->get();
         $potongan->transform(function ($value) {
             if($value->id_pajak_potongan < 12){
                 $btn  = false;
                 $status_message = $value->status_message;
                 $valid  = $value->is_valid;
                 if($valid == 1){
                     $btn_disable = true;
                     $btn_status  = "bg-success";
                 }else{
                     $btn_disable = false;
                     $btn_status  = "bg-danger";
                 }
             }else{
                 $btn  = true;
                 $btn_status  = "bg-success";
                 $status_message = "VALID";
                 $valid = 1;
             }
             $value->btn_disable = $btn;
             $value->status_message = $status_message;
             $value->is_valid = $valid;
             $value->btn_status = $btn_status;
             return $value;
         });


         return response()->json([
             'success' => true,
             'message' => 'Validasi Sudah Diproses',
             'potongan'  => $potongan,
             'payload'  => $req->all()
         ], 200);
     }


}
