<?php

namespace App\Http\Controllers\Kasda;

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


use App\Models\User;
use App\Models\SPP;
use App\Models\OPD;
use App\Models\Ta_Kasda as KASDA;
use App\Models\Ta_Kasda_Potongan as KASDA_POT;
use App\Models\Ta_Kasda_KDPotongan as KASDA_KDPOT;

use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;
use App\Http\Controllers\ApiAsis;
use App\Models\SPPD;

class KasdaController extends Controller
{
   public function index(){
      return 'oke';
   }

   public function Connection(Request $req){
        $hth  = "L99q0ksgaosiuknjbcijbp29lsd7rt0wi0s001";
        if($hth === $req->kodeh2h){
            $data  = [
              "status" => "200",
              "H2H" => true,
              "SERVER" => true,
              "message" => "Koneksi OK"
            ];
        }else{
            $data  = self::Error();
        }
        return response()->json([
            'result' => $data
        ], 200);
    }

    public function InqueryAll(Request $req){
         $hth  = "L99q0ksgaosiuknjbcijbp29lsd7rt0wi0s001";
         if($hth === $req->kodeh2h){
             $result  = [
               "status" => "200",
               "message" => "Request Data Sukses"
             ];
             $data  = KASDA::where('Cair',0)->where('is_proses',1)->get();
             $data->transform(function ($value) {
                $value->DateCreate = Carbon::parse($value->DateCreate)->format('m/d/Y');
                $value->Tgl_SP2D = Carbon::parse($value->Tgl_SP2D)->format('m/d/Y');
                $value->Tgl_SPM = Carbon::parse($value->Tgl_SPM)->format('m/d/Y');
                $value->Tgl_Penguji = Carbon::parse($value->Tgl_Penguji)->format('m/d/Y');
                $value->Kd_Urusan = "".$value->Kd_Urusan;
                $value->Kd_Bidang = "".$value->Kd_Bidang;
                $value->Kd_Unit = "".$value->Kd_Unit;
                $value->Kd_Sub = "".$value->Kd_Sub;
                $value->Tahun = "".$value->Tahun;
                $value->Nilai = "".$value->Nilai;
                $value->Cair = "".$value->Cair;
                return $value;
             });




             foreach($data as $dat){
                  $potongans  =  KASDA_POT::where('id_sp2d',$dat->id_sp2d)->get();
                  foreach($potongans as $dpots){
                      $potongan[] = [
                          "Tahun" => "".$dpots->Tahun,
                          "Kd_Urusan" => "".$dpots->Kd_Urusan,
                          "Kd_Bidang" => "".$dpots->Kd_Bidang,
                          "Kd_Unit" => "".$dpots->Kd_Unit,
                          "Kd_Sub" => "".$dpots->Kd_Sub,
                          "No_SP2D" => $dpots->No_SP2D,
                          "Kd_Rek_1" => "".$dpots->Kd_Rek_1,
                          "Kd_Rek_2" => "".$dpots->Kd_Rek_2,
                          "Kd_Rek_3" => "".$dpots->Kd_Rek_3,
                          "Kd_Rek_4" => "".$dpots->Kd_Rek_4,
                          "Kd_Rek_5" => "".$dpots->Kd_Rek_5,
                          "No_SPM" => "".$dpots->No_SPM,
                          "Jn_SPM" => "".$dpots->Jn_SPM,
                          "Nm_Rekening" => "".$dpots->Nm_Rekening,
                          "Nilai" => "".$dpots->Nilai.".0000",
                      ];
                  }
             }
             if(!isset($potongan)){
                $potongan = [];
             }


         }else{
             $result  = self::Error();
             $data  = [];
             $potongan = [];
         }
         return response()->json([
             'result' => $result,
             'data' => $data,
             'potongan' => $potongan,
         ], 200);
    }

    public function Payment(Request $req){
         $hth  = "L99q0ksgaosiuknjbcijbp29lsd7rt0wi0s001";
         if($hth === $req->kodeh2h){
            $pay  = KASDA::where('Cair',0)->where('No_SP2D',$req->sp2d)->where('is_proses',1)->first();
            if($pay){
               $data  = [
                   "status"  => "200",
                   "message"  => "Request Data Pembayaran Sukses",
                   "data"  => [
                     "SP2D"  => $pay->No_SP2D,
                     "No_SPM"  => $pay->No_SPM,
                     "nama"  => $pay->Nm_Penerima,
                     "NPWP"  => $pay->NPWP,
                     "Keterangan"  => $pay->Keterangan,
                     "Bank_Penerima"  => $pay->Bank_Penerima,
                     "Rek_Penerima"  => $pay->Rek_Penerima,
                     "Nama_Rekening"  => $pay->Nm_Penerima,
                     "Tgl_Penguji"  => Carbon::parse($pay->Tgl_Penguji)->format('m/d/Y'),
                     "Nm_Bank"  => $pay->Nm_Bank,
                     "No_Rekening"  => $pay->No_Rekening,
                     "Nilai"  => "".$pay->Nilai,
                     "DateCreate"  => Carbon::parse($pay->DateCreate)->format('m/d/Y'),
                     "Cair"  => "1"
                   ]
               ];
               KASDA::where('Cair',0)->where('No_SP2D',$req->sp2d)->update([
                  'Cair'  => 1,
                  'nip' => 'API-HIT'
               ]);

               $sp2d  = SPPD::where('nomor_sp_2_d',$req->sp2d)->with('spm')->first();
               if($sp2d){
                     $sp2d->spm->DataSPP();
                     try {
                         // INI UNTUK NOTIF KE BENDAHARA
                         $nip_bend  = $sp2d->spm->spp['nip_bend'];
                         $user_api = ApiAsis::GetHtHUsers($nip_bend);
                         if($user_api->success){
                             $hp = $user_api->data->hp;
                             $status = '*Sudah Cair*';
                             $kirim_pesan = '*Informasi Pengajuan*';
                             $kirim_pesan .= ' \n\n'.'*'.$sp2d->nomor_sp_2_d.'*';
                             $kirim_pesan .= ' \n\n'.$status.', Terimakasih';
                             ApiAsis::SendMessage($hp,$kirim_pesan);
                         }

                         // INI UNTUK NOTIF KE PETUGAS SP2D
                         $status = '*Sudah Cair*';
                         $kirim_pesan = '*Informasi Pengajuan*';
                         $kirim_pesan .= ' \n\n'.'*'.$sp2d->nomor_sp_2_d.'*';
                         $kirim_pesan .= ' \n\n'.$status.', Terimakasih';
                         $tim_sp2d  = DB::table('ta_otoritas')->where('id_ref',4)->where('lihat',1)->get();
                         foreach($tim_sp2d as $dat){
                             $user_api = ApiAsis::GetHtHUsers($dat->nip);
                             if($user_api->success){
                                 $hp = $user_api->data->hp;
                                 // ApiAsis::SendMessage($hp,$kirim_pesan);
                             }
                         }

                     }catch (\Exception $e) {
                         // $message = 'Transaksi Sukses !!!';
                         // $data = $e->getMessage();  
                     }
               }



            }else{
                $data  = [
                    'status'  => 404,
                    'message' => "Data tidak ditemukan"
                ];
            }
         }else{
             $data  = self::Error();
         }
         return response()->json([
             'result' => $data
         ], 200);
    }

    public function IndexRouteSatu(Request $req, $satu){
        return 'oke';
    }

    static function Error(){
        return $data  = [
          "status" => "404",
          "message" => "You dont have permission to access this service",
          "ip" => "36.68.26.52"
        ];
    }

}
