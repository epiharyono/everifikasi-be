<?php

namespace App\Http\Controllers\Sipd;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Hash;
use App\User;
use Input;
use Response;
use Auth;
use Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;


use App\Http\Controllers\Users\DataController as DATA;
use App\Http\Controllers\Users\UserController as Userc;

use GuzzleHttp\Client as GuzzleHttpClient;

class AgenSingkronController extends Controller
{
    static function SincSPPD($req){
        DB::table('ta_singkron')->insert([
            'act' => 'sppd',
            'act_id'  => 0,
            'status'  => 0,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Gagal Request Data.',
            'payload' => $req->all()
        ], 200);
    }

    static function SincSPP($req){
        DB::table('ta_singkron')->insert([
            'act' => 'spp',
            'act_id'  => 0,
            'status'  => 0,
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Gagal Request Data.',
            'payload' => $req->all()
        ], 200);
    }

    static function SincSPPCetak($req){
        $cek = DB::table('ta_singkron')->where('act','spp_cetak')->where('act_id',$req->id_spp)->first();
        if($cek){
            DB::table('ta_singkron')->where('id',$cek->id)->update([
                'status'  => 0,
            ]);
        }else{
            DB::table('ta_singkron')->insert([
                'act' => 'spp_cetak',
                'act_id'  => $req->id_spp,
                'id_skpd' => $req->id_skpd,
                'jenis' => $req->jenis_spp,
                'status'  => 0,
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Gagal Request Data.',
            'payload' => $req->all()
        ], 200);
    }

    static function SincSPPDCetak($req){
        $cek = DB::table('ta_singkron')->where('act','sppd_cetak')->where('act_id',$req->id_sp_2_d)->first();
        if($cek){
            DB::table('ta_singkron')->where('id',$cek->id)->update([
                'status'  => 0,
            ]);
        }else{
            DB::table('ta_singkron')->insert([
                'act' => 'sppd_cetak',
                'act_id'  => $req->id_sp_2_d,
                'id_skpd' => $req->id_skpd,
                'jenis' => $req->jenis,
                'status'  => 0,
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Gagal Request Data.',
            'payload' => $req->all()
        ], 200);
    }

    static function SincGaji($req){
        $cek = DB::table('ta_singkron')->where('act_id',$req->bulan_gaji)->where('id_skpd',$req->id_skpd)->where('act','gaji')->first();
        if($cek){
            DB::table('ta_singkron')->where('id',$cek->id)->update([
                'status'  => 0,
            ]);
        }else{
            DB::table('ta_singkron')->insert([
                'act' => 'gaji',
                'act_id'  => $req->bulan_gaji,
                'id_skpd' => $req->id_skpd,
                'jenis' => 0,
                'status'  => 0,
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Gagal Request Data.',
            'payload' => $req->all()
        ], 200);
    }

    static function SingkronNPD($req){
        $cek = DB::table('ta_singkron')->where('act','npd')->first();
        if($cek){
            DB::table('ta_singkron')->where('id',$cek->id)->update([
                'status'  => 0,
            ]);
        }else{
            DB::table('ta_singkron')->insert([
                'act' => 'npd',
                'act_id'  => 0,
                'id_skpd' => 0,
                'jenis' => 0,
                'status'  => 0,
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Gagal Request Data.',
            'payload' => $req->all()
        ], 200);
    }

    static function SincSPMCetak($req){
        $cek = DB::table('ta_singkron')->where('act','spm_cetak')->where('act_id',$req->id_spm)->first();
        if($cek){
            DB::table('ta_singkron')->where('id',$cek->id)->update([
                'status'  => 0,
            ]);
        }else{
            DB::table('ta_singkron')->insert([
                'act' => 'spm_cetak',
                'act_id'  => $req->id_spm,
                'id_skpd' => $req->id_skpd,
                'jenis' => $req->jenis,
                'status'  => 0,
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Gagal Request Data.',
            'payload' => $req->all()
        ], 200);
    }

    static function SingRekanan($req){
        $cek = DB::table('ta_singkron')->where('act','rekanan')->where('id_skpd',$req->id_skpd)->where('jenis',$req->nomor_rekening)->first();
        if($cek){
            DB::table('ta_singkron')->where('id',$cek->id)->update([
                'status'  => 0,
            ]);
        }else{
            DB::table('ta_singkron')->insert([
                'act' => 'rekanan',
                'act_id'  => 0,
                'id_skpd' => $req->id_skpd,
                'jenis' => $req->nomor_rekening,
                'status'  => 0,
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'Gagal Request Data.',
            'payload' => $req->all()
        ], 200);
    }



    static function GetRealtime($req){
        $spp       = DB::table('ta_singkron')->where('act','spp')->where('status',0)->first();
        if($spp){
            DB::table('ta_singkron')->where('id',$spp->id)->update(['status' => 1]);
        }
        $spp_search  = DB::table('ta_singkron')->where('act','spp_search')->where('status',0)->first();
        if($spp_search){
            DB::table('ta_singkron')->where('id',$spp_search->id)->update(['status' => 1]);
        }

        $spm       = DB::table('ta_singkron')->where('act','spm')->where('status',0)->first();
        if($spm){

            DB::table('ta_singkron')->where('id',$spm->id)->update(['status' => 1]);
        }

        $spm_cetak       = DB::table('ta_singkron')->where('act','spm_cetak')->where('status',0)->first();
        if($spm_cetak){
            DB::table('ta_singkron')->where('id',$spm_cetak->id)->update(['status' => 1]);
        }
        $spm_search = DB::table('ta_singkron')->where('act','spm_search')->where('status',0)->first();
        if($spm_search){
            DB::table('ta_singkron')->where('id',$spm_search->id)->update(['status' => 1]);
        }

        $spp_cetak = DB::table('ta_singkron')->where('act','spp_cetak')->where('status',0)->first();
        if($spp_cetak){
            DB::table('ta_singkron')->where('id',$spp_cetak->id)->update(['status' => 1]);
        }

        $sppd      = DB::table('ta_singkron')->where('act','sppd')->where('status',0)->first();
        if($sppd){
            DB::table('ta_singkron')->where('id',$sppd->id)->update(['status' => 1]);
        }

        $sppd_cetak      = DB::table('ta_singkron')->where('act','sppd_cetak')->where('status',0)->first();
        if($sppd_cetak){
            DB::table('ta_singkron')->where('id',$sppd_cetak->id)->update(['status' => 1]);
        }
        $sppd_search = DB::table('ta_singkron')->where('act','sp2d_search')->where('status',0)->first();
        if($sppd_search){
            DB::table('ta_singkron')->where('id',$sppd_search->id)->update(['status' => 1]);
        }

        $realtime  = DB::table('ta_singkron')->where('status',0)->count();
        $npd  = DB::table('ta_singkron')->where('act','npd')->where('status',0)->first();
        if($npd){
            DB::table('ta_singkron')->where('id',$npd->id)->update(['status' => 1]);
        }

        $gaji  = DB::table('ta_singkron')->where('act','gaji')->where('status',0)->first();
        if($gaji){
            DB::table('ta_singkron')->where('id',$gaji->id)->update(['status' => 1]);
        }

        $rekanan  = DB::table('ta_singkron')->where('act','rekanan')->where('status',0)->first();
        if($rekanan){
            DB::table('ta_singkron')->where('id',$rekanan->id)->update(['status' => 1]);
        }


        $payload = $req->all();
        $date = '2024-04-13T17:40:24+07:00';
        // $payload = Carbon::parse('2024-04-13T17:40:24+07:00')->toDateTimeString();
        // $payload = $carbonDate->toDateTimeString();

        return response()->json([
            'success' => true,
            'message' => 'Post Request Data.',
            'spp' => $spp,
            'spp_search' => $spp_search,
            'spm' => $spm,
            'spm_search' => $spm_search,
            'spm_cetak' => $spm_cetak,
            'spp_cetak' => $spp_cetak,
            'sppd' => $sppd,
            'sppd_cetak' => $sppd_cetak,
            'sppd_search' => $sppd_search,
            'realtime'  => $realtime,
            'npd'  => $npd,
            'gaji'  => $gaji,
            'rekanan'  => $rekanan,
            'payload' => $payload
        ], 200);
    }

}
