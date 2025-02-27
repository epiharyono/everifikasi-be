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
use App\Models\SPP;
// use App\Models\SPM;
use App\Models\SPPD;
use App\Models\GAJI;
// use App\Models\DPA;
// use App\Models\DPA_REKENING;

use App\Http\Controllers\Users\DataController as DATA;
use App\Http\Controllers\Users\UserController as Userc;

use GuzzleHttp\Client as GuzzleHttpClient;

class GajiController extends Controller
{
    static function SingkronGajiCetak($req){
        $message = 'oke'; $id_spp = 0;
        $data = $req->data;
        $data = json_decode($data, true);

        $data  = $data['items'];
        if(sizeOf($data)){
            foreach($data as $dat){
                $id_gaji_pegawai = $dat['id_gaji_pegawai'];

                $cek   = GAJI::where('id_gaji_pegawai',$id_gaji_pegawai)->first();
                if(!$cek){
                    GAJI::insert([
                        'id_gaji_pegawai' => $dat['id_gaji_pegawai'],
                        'id_daerah' => $dat['id_daerah'],
                        'id_skpd' => $dat['id_skpd'],
                        'bulan_gaji' => $dat['bulan_gaji'],
                        'tahun_gaji' => $dat['tahun_gaji'],
                        'jenis_gaji' => $dat['jenis_gaji'],
                        'nip_pegawai' => $dat['nip_pegawai'],
                        'nama_pegawai' => $dat['nama_pegawai'],
                        'nik_pegawai' => $dat['nik_pegawai'],
                        'npwp_pegawai' => $dat['npwp_pegawai'],
                        'tanggal_lahir_pegawai' => $dat['tanggal_lahir_pegawai'],
                        'id_tipe_jabatan' => $dat['id_tipe_jabatan'],
                        'nama_jabatan' => $dat['nama_jabatan'],
                        'eselon' => $dat['eselon'],
                        'pppk_pns' => $dat['pppk_pns'],
                        'golongan' => $dat['golongan'],
                        'mkg' => $dat['mkg'],
                        'alamat' => $dat['alamat'],
                        'status_pernikahan' => $dat['status_pernikahan'],
                        'jumlah_istri_suami' => $dat['jumlah_istri_suami'],
                        'jumlah_anak' => $dat['jumlah_anak'],
                        'jumlah_tanggungan' => $dat['jumlah_tanggungan'],
                        'is_pasangan_pns' => $dat['is_pasangan_pns'],
                        'nip_pasangan' => $dat['nip_pasangan'],
                        'kode_bank' => $dat['kode_bank'],
                        'nama_bank' => $dat['nama_bank'],
                        'nomor_rekening_bank_pegawai' => $dat['nomor_rekening_bank_pegawai'],
                        'belanja_gaji_pokok' => $dat['belanja_gaji_pokok'],
                        'perhitungan_suami_istri' => $dat['perhitungan_suami_istri'],
                        'perhitungan_anak' => $dat['perhitungan_anak'],
                        'belanja_tunjangan_keluarga' => $dat['belanja_tunjangan_keluarga'],
                        'belanja_tunjangan_jabatan' => $dat['belanja_tunjangan_jabatan'],
                        'belanja_tunjangan_fungsional' => $dat['belanja_tunjangan_fungsional'],
                        'belanja_tunjangan_fungsional_umum' => $dat['belanja_tunjangan_fungsional_umum'],
                        'belanja_tunjangan_beras' => $dat['belanja_tunjangan_beras'],
                        'belanja_tunjangan_pph' => $dat['belanja_tunjangan_pph'],
                        'belanja_pembulatan_gaji' => $dat['belanja_pembulatan_gaji'],
                        'belanja_iuran_jaminan_kesehatan' => $dat['belanja_iuran_jaminan_kesehatan'],
                        'belanja_iuran_jaminan_kecelakaan_kerja' => $dat['belanja_iuran_jaminan_kecelakaan_kerja'],
                        'belanja_iuran_jaminan_kematian' => $dat['belanja_iuran_jaminan_kematian'],
                        'belanja_iuran_simpanan_tapera' => $dat['belanja_iuran_simpanan_tapera'],
                        'belanja_iuran_pensiun' => $dat['belanja_iuran_pensiun'],
                        'tunjangan_khusus_papua' => $dat['tunjangan_khusus_papua'],
                        'tunjangan_jaminan_hari_tua' => $dat['tunjangan_jaminan_hari_tua'],
                        'potongan_iwp' => $dat['potongan_iwp'],
                        'potongan_pph_21' => $dat['potongan_pph_21'],
                        'zakat' => $dat['zakat'],
                        'bulog' => $dat['bulog'],
                        'jumlah_gaji_tunjangan' => $dat['jumlah_gaji_tunjangan'],
                        'jumlah_potongan' => $dat['jumlah_potongan'],
                        'jumlah_ditransfer' => $dat['jumlah_ditransfer'],
                        'id_transaksi' => $dat['id_transaksi'],
                        'status_transaksi' => $dat['status_transaksi'],
                        'transaksi_note' => $dat['transaksi_note'],
                        'bpd_code' => $dat['bpd_code'],
                        'nomor_transaksi_bank' => $dat['nomor_transaksi_bank'],
                        'nama_rekening_bank' => $dat['nama_rekening_bank'],
                        'is_manual' => $dat['is_manual'],
                        'created_at' => $dat['created_at'],
                        'created_by' => $dat['created_by'],
                        'updated_at' => $dat['updated_at'],
                        'updated_by' => $dat['updated_by'],
                        'deleted_at' => $dat['deleted_at'],
                        'deleted_by' => $dat['deleted_by'],
                    ]);
                }

            }
        }

        return response()->json([
            'success' => false,
            'status'  => 'success',
            'message' => $message,
            'data'  => sizeOf($data),
            'payload' => $req->all(),
        ], 200);
    }

}
