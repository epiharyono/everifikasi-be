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
use App\Models\SPP;
use App\Models\SPP_CETAK;


use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;
use App\Http\Controllers\ApiAsis;
use App\Http\Controllers\Sipd\SipdController as SIPD;

class BankController extends Controller
{

   static function GetAll($req){
        $success = false; $message = 'Otoritas Tidak Diizinkan';
        $super   = 1;
        $token = $req->bearerToken();

        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 401);
        }
        $nip  = $user['data']['nip'];

        $ha = HALocal::HakAksesUser($nip,5);
        if(!$ha['lihat']){
           return response()->json([
               'success' => false,
               'message' => $message.' '.$nip,
               'ha'  => $ha
           ],200);
        }
        $super = 0;
        $user  = User::where('nip_user',$nip)->where('status',1)->first();
        $success = true; $message = 'Sukses Get Data SPPD';
        $userl  = HALocal::GetTableUser($nip);
        $query  = SPPD::orderby('tanggal_spm','desc');
        if($req->search){
            $query->where('nomor_sp_2_d','LIKE','%'.$req->search.'%');
        }
        if($req->id_skpd){
            $query->where('id_skpd',$req->id_skpd);
        }
        $data  = $query->where('asis_final',1)->orderby('id_sp_2_d','desc')->paginate(10);
        $data->getCollection()->transform(function ($value) {
            $value->getStatus();
            return $value;
        });
        // $data  = $query->orderby('id_sp_2_d','desc')->paginate(10);

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
            'ha'  => $ha,
            'userl' => $userl,
            'user'  => $user,
            'payload' => $req->all(),
            'bank'  => true
        ], 200);

    }

    static function FindByID($id){
        $success = true; $message = 'Sukses Get Data SPPD';
        $data  = SPPD::where('id_sp_2_d',$id)->with('spm')->with('cetak')->with('potongan')->with('rekening')->with('bank')->first();
        if(isset($data->spm)){
            $data->spp_dokumen = DB::table('ta_spp_dokumen')->where('id_ref',4)->where('id_spp',$data->spm->id_spp)->get();
            $data->id_spp = $data->spm->id_spp;
            $data->spp  = SPP_CETAK::where('id_spp',$data->spm->id_spp)->first();
            $data->transaksi = DB::table('ta_transaksi')->where('id_spp',$data->spm->id_spp)->get();
        }else{
            $data->id_spp = 0;
            $data->spp_dokumen = '';
            $data->spp = '';
            $data->transaksi = [];
        }

        if($data->bulan_gaji){
            $data->data_gaji = DB::table('ta_gaji')->where('id_sp2d',$data->id_sp_2_d)->get();
        }else{
            $data->data_gaji = '';
        }

        return response()->json([
           'success' => $success,
           'message' => $message,
           'data'  => $data,
        ], 200);
    }

    static function Final($req){
        $success = true; $message = 'Sukses Update Data';
        $token = $req->bearerToken();
        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 401);
        }
        $nip  = $user['data']['nip'];
        $name  = $user['data']['name'];
        if($req->status == 1){
            DB::table('ta_sppd')->where('id_sp_2_d',$req->id_sp_2_d)->update([
                'asis_final_bank'  => 1,
            ]);
            DB::table('ta_kasda')->where('id_sp2d',$req->id_sp_2_d)->update([
                'is_proses'  => 1,
            ]);
        }elseif($req->status == 2){
            DB::table('ta_sppd')->where('id_sp_2_d',$req->id_sp_2_d)->update([
                'asis_final_bank'  => 2,
                'asis_final'  => 0,
            ]);
        }elseif($req->status == 3){
            DB::table('ta_sppd')->where('id_sp_2_d',$req->id_sp_2_d)->update([
                'asis_final_bank'  => 3,
            ]);
            DB::table('ta_kasda')->where('id_sp2d',$req->id_sp_2_d)->update([
                'is_proses'  => 2,
                'Cair'  => 1
            ]);
        }

        if($req->status == 1 || $req->status == 2){
              $sp2d  = SPPD::where('id_sp_2_d',$req->id_sp_2_d)->with('spm')->with('cetak')->with('potongan')->with('rekening')->first();
              if(!$sp2d){
                  return response()->json([
                      'success' => false,
                      'message' => 'SP2D Tidak Ditemukan'
                  ], 200);
              }
              $sp2d->kd_opd = DB::table('ta_kasda_kdopd')->where('id_skpd',$sp2d->id_sub_skpd)->first();
              $sp2d->spm->DataSPP();
              try {
                  // INI UNTUK NOTIF KE BENDAHARA
                  $nip_bend  = $sp2d->spm->spp['nip_bend'];
                  // $token = 'eyJhbGciOiJIUzI1NiIsImtpZCI6ImdseGJpSVVZTVhTM0ZPakxrMHNBZHRJWmZGYk9Zc3NaIiwidHlwIjoiSldUIn0.eyJleHAiOjE3MzA4OTYxOTQsImlkIjoxLCJuYW1lIjoiRVBJIEhBUllPTk8iLCJuaWsiOiIiLCJuaXAiOiIxOTg0MDkwODIwMTIxMjEwMDEiLCJzdWIiOjF9.mynYsfk89EPuMw_C1ckp3SOU6VzKUiPvtRPWPW1KPfk';
                  $user_api = ApiAsis::GetHtHUsers($nip_bend);
                  if($user_api->success){
                      $hp = $user_api->data->hp;
                      if($req->status == 1) $status  = 'Pengajuan Anda Saat ini Sudah Diproses Bank';
                      else $status = 'Status Pengajuan Anda ditolak pihak Bank';
                      $kirim_pesan = '*Informasi Pengajuan*';
                      $kirim_pesan .= ' \n\n'.'*'.$sp2d->nomor_sp_2_d.'*';
                      $kirim_pesan .= ' \n\n'.$status.', Terimakasih';
                      ApiAsis::SendMessage($hp,$kirim_pesan);
                  }

                  // INI UNTUK NOTIF KE PETUGAS SP2D
                  if($req->status == 1) $status  = 'Saat ini sudah diproses pihak Bank';
                  else $status = 'Status Pengajuan ditolak pihak bank';
                  $kirim_pesan = '*Informasi Pengajuan*';
                  $kirim_pesan .= ' \n\n'.'*'.$sp2d->nomor_sp_2_d.'*';
                  $kirim_pesan .= ' \n\n'.$status.', Terimakasih';
                  $tim_sp2d  = DB::table('ta_otoritas')->where('id_ref',4)->where('lihat',1)->get();
                  foreach($tim_sp2d as $dat){
                      $user_api = ApiAsis::GetHtHUsers($dat->nip);
                      if($user_api->success){
                          $hp = $user_api->data->hp;
                          ApiAsis::SendMessage($hp,$kirim_pesan);
                      }
                  }

              }catch (\Exception $e) {
                  $message = 'Transaksi Sukses !!!';
                  // $message = $e->getMessage();
              }
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'final'  => $req->status,
            'payload'  => $req->all(),
        ], 200);
    }

}
