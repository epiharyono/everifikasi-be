<?php

namespace App\Http\Controllers\SPP;

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

use App\Models\User;
use App\Models\SPP;

use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;
use App\Http\Controllers\Sipd\SipdController as SIPD;

class VeriController extends Controller
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

        $ha = HALocal::HakAksesUser($nip,3);
        if(!$ha['lihat']){
           return response()->json([
               'success' => false,
               'message' => $message.' '.$nip,
               'ha'  => $ha
           ],200);
        }
        $success = true; $message = 'Sukses Get Data Users';
        $userl  = HALocal::GetTableUser($nip);

        $query  = SPP::orderby('tanggal_spp','desc');
        if($req->search){
            $query->where('keterangan_spp','LIKE','%'.$req->search.'%');
        }
        if($req->id_skpd){
            $query->where('id_skpd',$req->id_skpd);
        }

        $data  = $query->where('asis_final_bend',1)->orderby('id_spp','desc')->paginate(10);
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
            'ha'  => $ha,
            'userl' => $userl,
            'payload' => $req->all()
        ], 200);

    }

    static function FindByID($id,$req){
        $success = false; $message = 'Otoritas Tidak Diizinkan';
        $super   = 1;
        $token = $req->bearerToken();
        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 401);
        }
        $nip  = $user['data']['nip'];

        $ha = HALocal::HakAksesUser($nip,3);
        if(!$ha['lihat']){
           return response()->json([
               'success' => false,
               'message' => $message.' '.$nip,
               'ha'  => $ha
           ],200);
        }
        $success = true; $message = 'Sukses Get Data SPP';

        $data  = SPP::where('id_spp',$id)->with('skpd')->with('cetak')->with('rekening')->with('ceklist')->first();
        if($data){
            $data->transaksi = DB::table('ta_transaksi')->where('id_spp',$data->id_spp)->get();
        }
        return response()->json([
           'success' => $success,
           'message' => $message,
           'data'  => $data,
        ], 200);
    }


    static function FinalVerifikasi($req){
        $message = 'Sukses Update Data';
        $success = true;
        $data = '';
        if($req->status == 1){
            $data = DB::table('ta_spp')->where('id_spp',$req->id_spp)->update([
                'asis_final_veri' => $req->status,
            ]);
            $bend = 1;
        }elseif($req->status == 2){
            $data = DB::table('ta_spp')->where('id_spp',$req->id_spp)->update([
                'asis_final_veri' => 2,
                'asis_final_bend' => 0,
            ]);
            $bend = 0;
        }
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
            'asis_final_veri' => $req->status,
            'asis_final_bend' => $bend,
        ], 200);
    }

    static function SaveNotes($req){
        $message = 'Sukses Simpan Catatan';
        $success = true;
        DB::table('ta_spp_dokumen')->where('id',$req->id)->where('id_spp',$req->id_spp)->update([
            'veri_notes' => $req->veri_notes,
        ]);
        $data = DB::table('ta_spp_dokumen')->where('id',$req->id)->first();
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
        ], 200);
    }

    static function UpdateCekList($req){
        $message = 'Data Berhasil Diupdate';
        $success = true;
        if($req->valid) $valid  = 1;
        else $valid = 0;
        DB::table('ta_ceklist')->where('id',$req->id)->update([
            'valid' => $valid,
        ]);
        return response()->json([
            'success' => $success,
            'message' => $message,
        ], 200);
    }

    static function GetUserAsis($req){
        $token = $req->bearerToken();

        return JWT::GetUser(3,$token);
    }

    static function SearchUserAsis($req){
        $token = $req->bearerToken();
        $data =  JWT::SearchUser($req,$token);

        return response()->json([
            'success' => false,
            'message' => "Gagal",
            'data'  => $data,
            'payload' => $req->all()
        ], 200);
    }

}
