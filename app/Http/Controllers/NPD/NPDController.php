<?php

namespace App\Http\Controllers\NPD;

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
use App\Models\NPD;
use App\Models\SPP;

use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;
use App\Http\Controllers\Sipd\SipdController as SIPD;

class NPDController extends Controller
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
        $query  = NPD::orderby('id_npd','desc');
        if($req->search){
            $query->where('keterangan_sp_2_d','LIKE','%'.$req->search.'%');
        }
        $data  = $query->where('id_skpd',$user->id_opd)->orderby('id_npd','desc')->paginate(10);
        // $data  = $query->paginate(10);

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

    static function FindByIDxxx($id){
        $success = true; $message = 'Sukses Get Data SPPD';
        $data  = NPD::where('id_sp_2_d',$id)->with('spm')->with('cetak')->with('potongan')->with('rekening')->first();
        if(isset($data->spm)){
            $data->spp_dokumen = DB::table('ta_spp_dokumen')->where('id_spp',$data->spm->id_spp)->get();
            $data->id_spp = $data->spm->id_spp;
            $data->spp  = SPP::where('id_spp',$data->spm->id_spp)->first();
        }else{
            $data->id_spp = 0;
            $data->spp_dokumen = '';
            $data->spp = '';
        }

        return response()->json([
           'success' => $success,
           'message' => $message,
           'data'  => $data,
        ], 200);
    }

}
