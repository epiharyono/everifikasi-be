<?php

namespace App\Http\Controllers\DPA;

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
use App\Models\SPM;
use App\Models\DPA;

use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;
use App\Http\Controllers\Sipd\SipdController as SIPD;

class DPAController extends Controller
{

   static function GetData($req){
        $success = true; $message = 'Otoritas Tidak Diizinkan'; $sipd = '';
        $tahun = date('Y');
        $data  = DPA::where('id_skpd',$req->id_skpd)->where('tahun',$tahun)->get();
        if(!sizeOf($data)){
            // $sipd = SIPD::SyncDPA($req);
        }
        return response()->json([
            'success' => $success,
            'message' => $message,
            'sipd'  => $sipd,
            'data'  => $data,
        ], 200);
    }

}
