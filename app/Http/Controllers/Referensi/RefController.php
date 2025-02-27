<?php

namespace App\Http\Controllers\Referensi;

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
use App\Models\OPD;

use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;

class RefController extends Controller
{

   static function GetRefDokumen(){
        $success = true; $message = 'Otoritas Tidak Diizinkan';
        $data  = DB::table('ref_dokumen')->where('id','!=',4)->get();
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
        ], 200);

    }

}
