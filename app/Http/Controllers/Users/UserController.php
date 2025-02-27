<?php

namespace App\Http\Controllers\Users;

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
use App\Models\OrdedrRinc;

use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;

class UserController extends Controller
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

        $ha = HALocal::HakAksesUser($nip,1);
        if(!$ha['lihat']){
           return response()->json([
               'success' => false,
               'message' => $message.' '.$nip,
               'ha'  => $ha
           ],200);
        }
        $super = 0;
        $success = true; $message = 'Sukses Get Data Users';
        $userl  = HALocal::GetTableUser($nip);
        $query  = User::orderby('id','desc');
        if($req->search){
            $query->where('nama_user','LIKE','%'.$req->search.'%');
        }
        if(!$super){
            // $query->where('id_opd',$userl->id_opd);
        }
        $data  = $query->paginate(10);

        // $success = false;
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
            'ha'  => $ha,
            'req' => $userl
        ], 200);

    }

    static function AddDataxxx($req){
         $success = false; $message = 'Otoritas Tidak Diizinkan';
         $user  = Auth::user();
         $super = 1;
         $super = 0;
         $ha = HALocal::HakAksesUser($nip,1);
         if(!$ha['tambah']){
            return response()->json([
                'success' => false,
                'message' => $message.' '.$nip,
                'ha'  => $ha
            ]);
         }

         $userl  = HALocal::GetTableUser($nip);
         $name   = DB::table('users')->where('nip',$req->nip)->where('id',$req->id)->value('name');
         if($super){
            $opd   = DB::connection('ASIPEDI')->table('ta_opd')->where('id',$req->id_opd)->first();
         }else{
            $opd   = DB::connection('ASIPEDI')->table('ta_opd')->where('id',$userl->id_opd)->first();
         }
         $slug  =  bin2hex(random_bytes(5));

         try {
             $success = true; $message = 'Sukses Tambah Data OPD';
             Ta_User::insert([
                'nama'  => $name,
                'nip' => $req->nip,
                'slug'  => $slug,
                'id_opd'  => $opd->id,
                'slug_opd'  => $opd->slug,
                'nm_opd'  => $opd->nama,
                'otoritas'  => $req->otoritas,
                'status'  => $req->status,
                'created_by'  => $user->name,
             ]);

         } catch (\Exception $e) {
            $message = $e->getMessage();
            $success = false;
         }

         return response()->json([
             'success' => $success,
             'message' => $message,
             'slug' => $slug
         ], 200);
    }

    static function FindByID($id){
         $success = true; $message = 'Sukses Get Data OPD';
         $data  = User::where('id_user',$id)->first();

         return response()->json([
             'success' => $success,
             'message' => $message,
             'data'  => $data,
         ], 200);
    }

    static function Update($id,$req){
         $success = false; $message = 'Data Tidak Diupdate';

         $data  = DB::table('users')->where('id',$req->id)->update([
            'id_opd'  => $req->id_opd,
            'status'  => $req->status,
         ]);

         if($data){
            $success = true; $message = 'Sukses Update Data';
         }

         return response()->json([
             'success' => $success,
             'message' => $message,
             'REQ'  => $req->all()
         ], 200);
    }

    static function GetOPD(){
        $opd   = DB::table('ta_opd')->get();
        $success = true; $message = 'Sukses Update Data';

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $opd
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
