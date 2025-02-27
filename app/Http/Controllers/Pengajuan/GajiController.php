<?php

namespace App\Http\Controllers\Pengajuan;

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
use App\Models\GAJI;

use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;
use App\Http\Controllers\Sipd\SipdController as SIPD;

class GajiController extends Controller
{

   static function GetData($req){
        $success = false; $message = 'Otoritas Tidak Diizinkan';
        $super   = 1;

        $sipd = SIPD::SyncGajiPegawai($req);

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

        $exp    = explode('-',$req->tanggal);
        if(sizeOf($exp) != 3)
        return response()->json([
           'success' => false,
           'message' => $message.' '.$nip,
        ],200);

        $tahun  = $exp[0];
        $bulan  = (int)$exp[1];

        $userl  = HALocal::GetTableUser($nip);
        $query  = GAJI::where('id_skpd',$req->id_skpd)->where('tahun_gaji',$tahun)->where('bulan_gaji',$bulan)->orderby('mkg','desc');
        if($req->search){
            $query->where('nama_pegawai','LIKE','%'.$req->search.'%');
        }
        if(!$super){
            // $query->where('id_opd',$userl->id_opd);
        }
        $data  = $query->paginate(100);
        if(sizeOf($data)){
            $success = true; $message = 'Sukses Get Data';
        }
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


}
