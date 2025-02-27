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
use App\Models\SPM;
use App\Models\SPM_CETAK;
use App\Models\SPM_POTONGAN;
use App\Models\SPP;
use App\Models\SPP_CETAK;
use App\Models\SPPD_POTONGAN;
use App\Models\Ta_Transaksi as TRANSAKSI;


use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;
use App\Http\Controllers\Sipd\SipdController as SIPD;

class SPPDController extends Controller
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

        $ha = HALocal::HakAksesUser($nip,2);
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
        $data  = $query->where('id_skpd',$user->id_opd)->orderby('id_sp_2_d','desc')->paginate(10);
        $data->getCollection()->transform(function ($value) {
            $value->getStatus();
            return $value;
        });

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
            'ha'  => $ha,
            'userl' => $userl,
            'user'  => $user,
            'payload' => $req->all()
        ], 200);

    }

    static function FindByID($id){
        $success = true; $message = 'Sukses Get Data SPPD';
        $data  = SPPD::where('id_sp_2_d',$id)->with('spm')->with('spm_cetak')->with('spm_dokumen')->with('cetak')
                 ->with('ceklist')->with('potongan')->with('rekening')->with('bank')->first();
        if(isset($data->spm)){
            $data->spp_dokumen = DB::table('ta_spp_dokumen')->where('id_spp',$data->spm->id_spp)->get();
            $data->id_spp = $data->spm->id_spp;
            $data->spp  = SPP::where('id_spp',$data->spm->id_spp)->first();
            $data->spp_cetak  = SPP_CETAK::where('id_spp',$data->spm->id_spp)->first();
            $data->transaksi = TRANSAKSI::where('id_spm',$data->spm->id_spm)->get();
            $data->transaksi->transform(function ($value) {
                $value->cek_status();
                return $value;
            });

            if(sizeOf($data->transaksi) == 1){
                if(!$data->jumlah_ditransfer){
                    $jml_pot = SPM_POTONGAN::where('id_spm',$data->spm->id_spm)->sum('nilai_spp_pajak_potongan');
                    $nil_sp2d = $data->nilai_sp_2_d;
                    DB::table('ta_transaksi')->where('id_spm',$data->spm->id_spm)->update([
                        'jumlah_ditransfer' => $nil_sp2d - $jml_pot,
                    ]);
                }
            }

        }else{
            $data->id_spp = 0;
            $data->spp_dokumen = '';
            $data->spp = '';
            $data->transaksi = [];
        }

        $data->potongan->transform(function ($value) {
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
           'success' => $success,
           'message' => $message,
           'data'  => $data,
           'btn'  => 'bg-success'
        ], 200);
    }

    static function EditPotongan($req,$id){
        $success = true; $message = 'Sukses Edit Potongan';
        DB::table('ta_sppd_potongan')->where('id',$req->id)->where('id_sp_2_d',$req->id_sp_2_d)->update([
            'id_billing'  => $req->id_billing,
            'nilai_sp2d_pajak_potongan' => $req->nilai_sp2d_pajak_potongan,
            'mata_anggaran' => $req->mata_anggaran,
            'jenis_setoran' => $req->jenis_setoran,
            'masa_pajak' => $req->masa_pajak,
            'tahun_pajak' => $req->tahun_pajak,
            'is_valid'  => 0,
            'status_message'  => 'Belum Divalidasi',
        ]);
        $data  = DB::table('ta_sppd_potongan')->where('id_sp_2_d',$req->id_sp_2_d)->get();
        $data->transform(function ($value) {
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
           'success' => $success,
           'message' => $message,
           'data' => $data,
           'payload'  => $req->all(),
        ], 200);

    }

}
