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

// use App\Models\User;
// use App\Models\OPD;
// use App\Models\SPP;
// use App\Models\SPM;
use App\Models\SPM;
use App\Models\SPM_CETAK;
// use App\Models\GAJI;
// use App\Models\DPA;
// use App\Models\DPA_REKENING;

use App\Http\Controllers\Users\DataController as DATA;
use App\Http\Controllers\Users\UserController as Userc;

use GuzzleHttp\Client as GuzzleHttpClient;

class RekananController extends Controller
{

    static function SingkronRekanan($req){
        $message = ''; $data = '';
        $data = $req->data_rekanan;
        $data = json_decode($data, true);
        $id   =  $req->id_spm;
        $jenis = $req->tipe;

        if(sizeOf($data)){
            foreach($data as $dat){
                $cek  = DB::table('ta_rekanan')->where('nomor_rekening',$dat['nomor_rekening'])->first();
                if(!$cek){
                    DB::table('ta_rekanan')->insert([
                        "tahun" =>  $dat['tahun'],
                        "id_daerah" =>  $dat['id_daerah'],
                        "id_skpd" =>  $dat['id_skpd'],
                        "nomor_rekening" =>  $dat['nomor_rekening'],
                        "nama_rekening" =>  $dat['nama_rekening'],
                        "id_bank" =>  $dat['id_bank'],
                        "nama_bank" =>  $dat['nama_bank'],
                        "cabang_bank" =>  $dat['cabang_bank'],
                        "nama_tujuan" =>  $dat['nama_tujuan'],
                        "nama_perusahaan" =>  $dat['nama_perusahaan'],
                        "alamat_perusahaan" =>  $dat['alamat_perusahaan'],
                        "telepon_perusahaan" =>  $dat['telepon_perusahaan'],
                        "npwp" =>  $dat['npwp'],
                        "nik" =>  $dat['nik'],
                        "jenis_rekanan" =>  $dat['jenis_rekanan'],
                        "kategori_rekanan" =>  $dat['kategori_rekanan'],
                        "is_valid" =>  $dat['is_valid'],
                        "is_locked" =>  $dat['is_locked'],
                        "created_at" =>  $dat['created_at'],
                        "created_by" =>  $dat['created_by'],
                        "updated_at" =>  $dat['updated_at'],
                        "updated_by" =>  $dat['updated_by'],
                        "deleted_at" =>  $dat['deleted_at'],
                        "deleted_by" =>  $dat['deleted_by']
                    ]);

                }
            }
        }



        return response()->json([
            'success' => false,
            'status'  => 'success',
            'message' => $message,
            'data'  => $data,
            'payload' => $req->all(),
        ], 200);
    }

}
