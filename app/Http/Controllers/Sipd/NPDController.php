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

// use App\Models\User;
// use App\Models\OPD;
// use App\Models\SPP;
// use App\Models\SPM;
use App\Models\SPM;
// use App\Models\GAJI;
// use App\Models\DPA;
// use App\Models\DPA_REKENING;

use App\Http\Controllers\Users\DataController as DATA;
use App\Http\Controllers\Users\UserController as Userc;

use GuzzleHttp\Client as GuzzleHttpClient;

class NPDController extends Controller
{

    static function SingkronNPD($req){
        $message = 'Sing SingSP2DDetail';
        $header = '';
        $data = $req->data;
        $data = json_decode($data, true);

        try {
            if(sizeOf($data)){
              foreach($data as $dat){
                $id   = $dat['id_npd'];
                $cek   = DB::table('ta_npd')->where('id_npd',$dat['id_npd'],)->first();
                if(!$cek){

                    DB::table('ta_npd')->insert([
                      "id_npd"  => $dat["id_npd"],
                      "nomor_npd" => $dat["nomor_npd"],
                      "tahun" => $dat["tahun"],
                      "id_daerah" => $dat["id_daerah"],
                      "id_unit" => $dat["id_unit"],
                      "id_skpd" => $dat["id_skpd"],
                      "nilai_npd" => $dat["nilai_npd"],
                      "nilai_npd_disetujui" => $dat["nilai_npd_disetujui"],
                      "tanggal_npd" => $dat["tanggal_npd"],
                      "tanggal_npd_selesai" => $dat["tanggal_npd_selesai"],
                      "keterangan_npd" => $dat["keterangan_npd"],
                      "is_verifikasi_npd" => $dat["is_verifikasi_npd"],
                      "verifikasi_npd_at" => $dat["verifikasi_npd_at"],
                      "verifikasi_npd_by" => $dat["verifikasi_npd_by"],
                      "nomor_verifikasi" => $dat["nomor_verifikasi"],
                      "is_npd_panjar" => $dat["is_npd_panjar"],
                      "kondisi_selesai" => $dat["kondisi_selesai"],
                      "selesai_at" => $dat["selesai_at"],
                      "selesai_by" => $dat["selesai_by"],
                      "nomor_selesai" => $dat["nomor_selesai"],
                      "nilai_pengembalian" => $dat["nilai_pengembalian"],
                      "nilai_kurang_bayar" => $dat["nilai_kurang_bayar"],
                      "nomor_kurang_lebih" => $dat["nomor_kurang_lebih"],
                      "kurang_lebih_at" => $dat["kurang_lebih_at"],
                      "kurang_lebih_by" => $dat["kurang_lebih_by"],
                      "is_validasi_npd" => $dat["is_validasi_npd"],
                      "validasi_npd_at" => $dat["validasi_npd_at"],
                      "validasi_npd_by" => $dat["validasi_npd_by"],
                      "is_tbp" => $dat["is_tbp"],
                      "tbp_at" => $dat["tbp_at"],
                      "tbp_by" => $dat["tbp_by"],
                      "id_jadwal" => $dat["id_jadwal"],
                      "id_tahap" => $dat["id_tahap"],
                      "status_tahap" => $dat["status_tahap"],
                      "kode_tahap" => $dat["kode_tahap"],
                      "created_at" => $dat["created_at"],
                      "created_by" => $dat["created_by"],
                      "updated_at" => $dat["updated_at"],
                      "updated_by" => $dat["updated_by"],
                      "deleted_at" => $dat["deleted_at"],
                      "deleted_by" => $dat["deleted_by"],
                      "kode_skpd" => $dat["kode_skpd"],
                      "nama_skpd" => $dat["nama_skpd"],
                      "kode_sub_skpd" => $dat["kode_sub_skpd"],
                      "nama_sub_skpd" => $dat["nama_sub_skpd"],
                      "total_pertanggungjawaban" => $dat["total_pertanggungjawaban"],
                    ]);
                }

              }

            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error Get API', 'ex'=>$e ];
        }

        return response()->json([
            'success' => false,
            'status'  => 'success',
            'message' => $message,
            'id_spm'  => $id,
            'data'  => $data,
            'payload' => $req->jenis,
        ], 200);
    }

}
