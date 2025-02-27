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
// use App\Models\GAJI;
// use App\Models\DPA;
// use App\Models\DPA_REKENING;

use App\Http\Controllers\Users\DataController as DATA;
use App\Http\Controllers\Users\UserController as Userc;

use GuzzleHttp\Client as GuzzleHttpClient;

class SPPController extends Controller
{
    static function SingkronSPP($req){
        $message = 'oke'; $id_spp = 0;
        $data = $req->data;
        $data = json_decode($data, true);

        if(sizeOf($data)){
            foreach($data as $dat){

                $id_spp = $dat['id_spp'];

                $cek   = SPP::where('id_spp',$id_spp)->first();
                if(!$cek){
                    SPP::create([
                      'id_spp' => $id_spp,
                      'bulan_gaji' => $dat['bulan_gaji'],
                      'bulan_tpp' => $dat['bulan_tpp'],
                      'created_at' => Carbon::parse($dat['created_at'])->toDateTimeString(),
                      'created_by' => $dat['created_by'],
                      'deleted_at' => Carbon::parse($dat['deleted_at'])->toDateTimeString(),
                      'deleted_by' => $dat['deleted_by'],
                      'details' => $dat['details'],
                      'id_ba' => $dat['id_ba'],
                      'id_daerah' => $dat['id_daerah'],
                      'id_jadwal' => $dat['id_jadwal'],
                      'id_kontrak' => $dat['id_kontrak'],
                      'id_lpj_gu' => $dat['id_lpj_gu'],
                      'id_pegawai_pa_kpa' => $dat['id_pegawai_pa_kpa'],
                      'id_pegawai_pptk' => $dat['id_pegawai_pptk'],
                      'id_pengajuan_tu' => $dat['id_pengajuan_tu'],
                      'id_skpd' => $dat['id_skpd'],
                      'id_sub_skpd' => $dat['id_sub_skpd'],
                      'id_sumber_dana' => $dat['id_sumber_dana'],
                      'id_tahap' => $dat['id_tahap'],
                      'id_unit' => $dat['id_unit'],
                      'is_gaji' => $dat['is_gaji'],
                      'is_kunci_rekening_spp' => $dat['is_kunci_rekening_spp'],
                      'is_rekanan_upload' => $dat['is_rekanan_upload'],
                      'is_spm' => $dat['is_spm'],
                      'is_status_perubahan' => $dat['is_status_perubahan'],
                      'is_tpp' => $dat['is_tpp'],
                      'is_verifikasi_spp' => $dat['is_verifikasi_spp'],
                      'jenis_gaji' => $dat['jenis_gaji'],
                      'jenis_ls_spp' => $dat['jenis_ls_spp'],
                      'jenis_spp' => $dat['jenis_spp'],
                      'keterangan_spp' => $dat['keterangan_spp'],
                      'keterangan_verifikasi_spp' => $dat['keterangan_verifikasi_spp'],
                      'kode_tahap' => $dat['kode_tahap'],
                      'nilai_materai_spp' => $dat['nilai_materai_spp'],
                      'nilai_spp' => $dat['nilai_spp'],
                      'nilai_verifikasi_spp' => $dat['nilai_verifikasi_spp'],
                      'nomor_spp' => $dat['nomor_spp'],
                      'rekanan_nama_perusahaan' => $dat['rekanan_nama_perusahaan'],
                      'rekanan_nama_rekening' => $dat['rekanan_nama_rekening'],
                      'rekanan_nama_tujuan' => $dat['rekanan_nama_tujuan'],
                      'rekanan_nik' => $dat['rekanan_nik'],
                      'rekanan_nomor_rekening' => $dat['rekanan_nomor_rekening'],
                      'status_perubahan_at' => Carbon::parse($dat['status_perubahan_at'])->toDateTimeString(),
                      'status_perubahan_by' => $dat['status_perubahan_by'],
                      'status_tahap' => $dat['status_tahap'],
                      'tahun' => $dat['tahun'],
                      'tahun_gaji' => $dat['tahun_gaji'],
                      'tahun_tpp' => $dat['tahun_tpp'],
                      'tanggal_spp' => Carbon::parse($dat['tanggal_spp'])->toDateTimeString(),
                      'updated_at' => Carbon::parse($dat['updated_at'])->toDateTimeString(),
                      'updated_by' => $dat['updated_by'],
                      'verifikasi_spp_at' => Carbon::parse($dat['verifikasi_spp_at'])->toDateTimeString(),
                      'verifikasi_spp_by' => $dat['verifikasi_spp_by'],
                    ]);
                }

            }
        }

        return response()->json([
            'success' => false,
            'status'  => 'success',
            'message' => $message,
            'id_spp'  => $id_spp,
            'data'  => $data,
            'payload' => $req->all(),
        ], 200);
    }

    static function SingkronSPPCetak($req){
        $message = 'Sing Singkron SPP Cetak';
        $header = '';
        $data = $req->data;
        $data = json_decode($data, true);
        $id   = $req->id_spp;
        try {
            if(sizeOf($data)){

                  $jenis = $req->tipe;
                  if($jenis == 'LS'){
                      $header  = $data['header'];
                      $cek  = DB::table('ta_spp_cetak')->where('id_spp',$id)->first();
                      if(!$cek){
                          DB::table('ta_spp_cetak')->insert([
                              'id_spp'  => $id,
                              'tahun' => $header['tahun'],
                              'nomor_transaksi' => $header['nomor_transaksi'],
                              'tanggal_transaksi' => $header['tanggal_transaksi'],
                              'nama_pa_kpa' => $header['nama_pa_kpa'],
                              'nip_pa_kpa' => $header['nip_pa_kpa'],
                              'nama_skpd' => $header['nama_skpd'],
                              'nama_sub_skpd' => $header['nama_sub_skpd'],
                              'jabatan_pa_kpa' => $header['jabatan_pa_kpa'],
                              'nama_pptk' => $header['nama_pptk'],
                              'nip_pptk' => $header['nip_pptk'],
                              'no_rek_bp_bpp' => $header['no_rek_bp_bpp'],
                              'nama_rek_bp_bpp' => $header['nama_rek_bp_bpp'],
                              'bank_bp_bpp' => $header['bank_bp_bpp'],
                              'npwp_bp_bpp' => $header['npwp_bp_bpp'],
                              'nama_bp_bpp' => $header['nama_bp_bpp'],
                              'nip_bp_bpp' => $header['nip_bp_bpp'],
                              'jabatan_bp_bpp' => $header['jabatan_bp_bpp'],
                              'keterangan' => $header['keterangan'],
                          ]);
                      }
                      $detail  = $data['detail'];
                      foreach($detail as $dat){
                          $cek  = DB::table('ta_spp_rekening')->where('id_spp',$id)->where('kode_rekening',$dat['kode_rekening'])->first();
                          if($cek){
                              DB::table('ta_spp_rekening')->where('id',$cek->id)->update([
                                  'kode_rekening' => $dat['kode_rekening'],
                                  'uraian' => $dat['uraian'],
                                  'jumlah' => $dat['jumlah'],
                              ]);
                          }else{
                              if($dat['kode_rekening'] != ''){
                                  DB::table('ta_spp_rekening')->insert([
                                      'id_spp'  => $id,
                                      'kode_rekening' => $dat['kode_rekening'],
                                      'uraian' => $dat['uraian'],
                                      'jumlah' => $dat['jumlah'],
                                  ]);
                              }
                          }
                      }
                  }
                  else{
                      $header  = $data['header'];
                      $cek  = DB::table('ta_spp_cetak')->where('id_spp',$id)->first();
                      if(!$cek){
                          DB::table('ta_spp_cetak')->insert([
                              'id_spp'  => $id,
                              'nama_daerah' => $header['nama_daerah'],
                              // 'nama_ibu_kota' => $header['nama_ibu_kota'],
                              'tahun' => $header['tahun'],
                              'nomor_transaksi' => $header['nomor_transaksi'],
                              'tanggal_transaksi' => $header['tanggal_transaksi'],
                              'nama_skpd' => $header['nama_skpd'],
                              'nama_pa_kpa' => $header['nama_pa_kpa'],
                              'nip_pa_kpa' => $header['nip_pa_kpa'],
                              'jabatan_pa_kpa' => $header['jabatan_pa_kpa'],
                              'no_rek_bp_bpp' => $header['no_rek_bp_bpp'],
                              'nama_rek_bp_bpp' => $header['nama_rek_bp_bpp'],
                              'bank_bp_bpp' => $header['bank_bp_bpp'],
                              'npwp_bp_bpp' => $header['npwp_bp_bpp'],
                              'keterangan' => $header['keterangan'],
                              'nilai' => $header['nilai'],
                              'nama_bp_bpp' => $header['nama_bp_bpp'],
                              'nip_bp_bpp' => $header['nip_bp_bpp'],
                              // 'jababtan_bp_bpp' => $header['jababtan_bp_bpp'],
                              // 'spd_date' => $header['spd_date'],
                              // 'nomor_spd' => $header['nomor_spd'],
                              // 'nilai_spd' => $header['nilai_spd'],
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
            'data'  => $data,
            'payload' => $req->tipe,
        ], 200);
    }

}
