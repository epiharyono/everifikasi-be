<?php

namespace App\Http\Controllers\Sipd;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Hash;
use App\Models\User;
use App\Models\OPD;
use App\Models\SPP;
use App\Models\SPM;
use App\Models\SPPD;
use App\Models\GAJI;
use App\Models\DPA;
use App\Models\DPA_REKENING;
use Input;
use Response;
use Auth;
use Crypt;
use Date;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

use App\Http\Controllers\Sipd\APIController as API;

use GuzzleHttp\Client as GuzzleHttpClient;

class SipdController extends Controller
{

    static function SyncPengguna(){
          $token  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJTSVBEX0FVVEhfU0VSVklDRSIsInN1YiI6IjUwMS41MjciLCJleHAiOjE3MjY3NzUyODMsImlhdCI6MTcyNjU1OTI4MywidGFodW4iOjIwMjQsImlkX3VzZXIiOjUwMSwiaWRfZGFlcmFoIjo1MjcsImtvZGVfcHJvdmluc2kiOiIyMSIsImlkX3NrcGQiOjAsImlkX3JvbGUiOjksImlkX3BlZ2F3YWkiOjUwMSwic3ViX2RvbWFpbl9kYWVyYWgiOiJrZXB1bGF1YW5hbmFtYmFza2FiIn0.6t8w6706vE-Ep9d9O9Z-uzvp0hggZoEC2stUUE-bC3Q';

          $method = 'GET';
          $a      = 1;
          while ($a <= 1000) {
              try {
                  $url    = '/auth/strict/user-manager?page='.$a.'&limit=5';
                  $data = API::SIPD($token,$url,$method);
                  if(!$data){
                      break;
                  }else{
                      foreach($data as $dat){
                          $datas =  $dat;
                          $cek   = User::where('id_user',$dat->id_user)->first();
                          if(!$cek){
                              User::create([
                                'id_user' => $dat->id_user,
                                'id_daerah' => $dat->id_daerah,
                                'nip_user' => $dat->nip_user,
                                'nama_user' => $dat->nama_user,
                                'id_pang_gol' => $dat->id_pang_gol,
                                'nik_user' => $dat->nik_user,
                                'npwp_user' => $dat->npwp_user,
                                'alamat' => $dat->alamat,
                                'lahir_user' => $dat->lahir_user,
                              ]);
                          }
                      }
                  }
                  $a++;
              } catch (Throwable $e) {
                  return ['success' => false, 'message' => 'Error Get API'];
              }
          }

          return response()->json([
              'success' => true,
              'message' => 'Sukses Singkron Data',
              'data'  => $data,
              'page'  => $a,
          ], 200);
    }

    static function SyncOPD(){
          $token  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJTSVBEX0FVVEhfU0VSVklDRSIsInN1YiI6IjUwMS41MjciLCJleHAiOjE3MjY3NzUyODMsImlhdCI6MTcyNjU1OTI4MywidGFodW4iOjIwMjQsImlkX3VzZXIiOjUwMSwiaWRfZGFlcmFoIjo1MjcsImtvZGVfcHJvdmluc2kiOiIyMSIsImlkX3NrcGQiOjAsImlkX3JvbGUiOjksImlkX3BlZ2F3YWkiOjUwMSwic3ViX2RvbWFpbl9kYWVyYWgiOiJrZXB1bGF1YW5hbmFtYmFza2FiIn0.6t8w6706vE-Ep9d9O9Z-uzvp0hggZoEC2stUUE-bC3Q';

          $method = 'GET';
          $datas  = [];
          $a      = 1;
          while ($a <= 1000) {
              try {
                  $url    = '/pengeluaran/strict/spd/pembuatan?page='.$a.'&limit=1000';
                  $data = API::SIPD($token,$url,$method);
                  if(!$data){
                      break;
                  }else{
                      foreach($data as $dat){
                          $datas[] =  $dat;
                          $cek   = OPD::where('id_skpd',$dat->id_skpd)->first();
                          if(!$cek){
                              OPD::create([
                                'tahun' => $dat->tahun,
                                'id_daerah' => $dat->id_daerah,
                                'id_skpd' => $dat->id_skpd,
                                'kode_skpd' => $dat->kode_skpd,
                                'nama_skpd' => $dat->nama_skpd,
                                'nilai' => $dat->nilai,
                                'nilai_rak' => $dat->nilai_rak,
                                'status' => $dat->status,
                              ]);
                          }
                      }
                  }
                  $a++;
              } catch (Exception $e) {
                  return ['success' => false, 'message' => 'Error Get API', 'ex'=>$e ];
              }
          }

          return response()->json([
              'success' => true,
              'message' => 'Sukses Singkron Data',
              'data'  => [],
          ], 200);
    }

    static function SyncSPP(){
          $token  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJTSVBEX0FVVEhfU0VSVklDRSIsInN1YiI6IjUwMS41MjciLCJleHAiOjE3MjY3NzUyODMsImlhdCI6MTcyNjU1OTI4MywidGFodW4iOjIwMjQsImlkX3VzZXIiOjUwMSwiaWRfZGFlcmFoIjo1MjcsImtvZGVfcHJvdmluc2kiOiIyMSIsImlkX3NrcGQiOjAsImlkX3JvbGUiOjksImlkX3BlZ2F3YWkiOjUwMSwic3ViX2RvbWFpbl9kYWVyYWgiOiJrZXB1bGF1YW5hbmFtYmFza2FiIn0.6t8w6706vE-Ep9d9O9Z-uzvp0hggZoEC2stUUE-bC3Q';

          $method = 'GET';
          $datas  = [];
          $a      = 1;
          while ($a <= 10000) {
              try {
                  $url    = '/pengeluaran/strict/spp/pembuatan/index?jenis=&status=final&page='.$a;
                  $data = API::SIPD($token,$url,$method);
                  if(!$data){
                      break;
                  }else{
                      foreach($data as $dat){
                          $datas[] =  $dat;
                          // $tanggal_spp = new Date($dat->tanggal_spp);
                          $tanggal_spp = date("Y-m-d H:i:s", strtotime($dat->tanggal_spp));
                          $verifikasi_spp_at = date("Y-m-d H:i:s", strtotime($dat->verifikasi_spp_at));
                          $status_perubahan_at = date("Y-m-d H:i:s", strtotime($dat->status_perubahan_at));
                          $updated_at = date("Y-m-d H:i:s", strtotime($dat->updated_at));
                          $cek   = SPP::where('id_spp',$dat->id_spp)->first();
                          if(!$cek){
                              SPP::create([
                                'id_spp' => $dat->id_spp,
                                'rekanan_nama_perusahaan' => $dat->rekanan_nama_perusahaan,
                                'rekanan_nomor_rekening' => $dat->rekanan_nomor_rekening,
                                'rekanan_nama_rekening' => $dat->rekanan_nama_rekening,
                                'rekanan_nama_tujuan' => $dat->rekanan_nama_tujuan,
                                'rekanan_nik' => $dat->rekanan_nik,
                                'nomor_spp' => $dat->nomor_spp,
                                'tahun' => $dat->tahun,
                                'id_daerah' => $dat->id_daerah,
                                'id_unit' => $dat->id_unit,
                                'id_skpd' => $dat->id_skpd,
                                'id_sub_skpd' => $dat->id_sub_skpd,
                                'nilai_spp' => $dat->nilai_spp,
                                'tanggal_spp' => $tanggal_spp,
                                'keterangan_spp' => $dat->keterangan_spp,
                                'is_verifikasi_spp' => $dat->is_verifikasi_spp,
                                'verifikasi_spp_by' => $dat->verifikasi_spp_by,
                                'verifikasi_spp_at' => $verifikasi_spp_at,
                                'nilai_verifikasi_spp' => $dat->nilai_verifikasi_spp,
                                'nilai_materai_spp' => $dat->nilai_materai_spp,
                                'keterangan_verifikasi_spp' => $dat->keterangan_verifikasi_spp,
                                'jenis_spp' => $dat->jenis_spp,
                                'jenis_ls_spp' => $dat->jenis_ls_spp,
                                'is_kunci_rekening_spp' => $dat->is_kunci_rekening_spp,
                                'is_spm' => $dat->is_spm,
                                'is_gaji' => $dat->is_gaji,
                                'jenis_gaji' => $dat->jenis_gaji,
                                'bulan_gaji' => $dat->bulan_gaji,
                                'tahun_gaji' => $dat->tahun_gaji,
                                'is_tpp' => $dat->is_tpp,
                                'bulan_tpp' => $dat->bulan_tpp,
                                'tahun_tpp' => $dat->tahun_tpp,
                                'id_pegawai_pptk' => $dat->id_pegawai_pptk,
                                'id_pegawai_pa_kpa' => $dat->id_pegawai_pa_kpa,
                                'is_rekanan_upload' => $dat->is_rekanan_upload,
                                'id_kontrak' => $dat->id_kontrak,
                                'id_lpj_gu' => $dat->id_lpj_gu,
                                'id_pengajuan_tu' => $dat->id_pengajuan_tu,
                                'id_ba' => $dat->id_ba,
                                'id_sumber_dana' => $dat->id_sumber_dana,
                                'is_status_perubahan' => $dat->is_status_perubahan,
                                'status_perubahan_at' => $status_perubahan_at,
                                'status_perubahan_by' => $dat->status_perubahan_by,
                                'id_jadwal' => $dat->id_jadwal,
                                'id_tahap' => $dat->id_tahap,
                                'status_tahap' => $dat->status_tahap,
                                'kode_tahap' => $dat->kode_tahap,
                                'created_at' => $dat->created_at,
                                'created_by' => $dat->created_by,
                                'updated_at' => $updated_at,
                                'updated_by' => $dat->updated_by,
                                'deleted_at' => $dat->deleted_at,
                                'deleted_by' => $dat->deleted_by,
                                'details' => $dat->details,
                              ]);
                          }
                      }
                  }
                  $a++;
              } catch (Exception $e) {
                  return ['success' => false, 'message' => 'Error Get API', 'ex'=>$e ];
              }
          }

          return response()->json([
              'success' => true,
              'message' => 'Sukses Singkron Data',
              'data'  => [],
          ], 200);
    }

    static function SyncSPM(){
          $token  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJTSVBEX0FVVEhfU0VSVklDRSIsInN1YiI6IjUwMS41MjciLCJleHAiOjE3MjY3NzUyODMsImlhdCI6MTcyNjU1OTI4MywidGFodW4iOjIwMjQsImlkX3VzZXIiOjUwMSwiaWRfZGFlcmFoIjo1MjcsImtvZGVfcHJvdmluc2kiOiIyMSIsImlkX3NrcGQiOjAsImlkX3JvbGUiOjksImlkX3BlZ2F3YWkiOjUwMSwic3ViX2RvbWFpbl9kYWVyYWgiOiJrZXB1bGF1YW5hbmFtYmFza2FiIn0.6t8w6706vE-Ep9d9O9Z-uzvp0hggZoEC2stUUE-bC3Q';

          $method = 'GET';
          $datas  = [];
          $a      = 1;
          $a      = 760;
          while ($a <= 10000) {
              try {
                  $url    = '/pengeluaran/strict/spm/index?jenis=&status=final&page='.$a;
                  // strict/spm/index?jenis=LS&status=draft&page=1&limit=10&nomor_spm=&keterangan_spm=
                  $data = API::SIPD($token,$url,$method);
                  if(!$data){
                      break;
                  }else{
                      foreach($data as $dat){
                          $datas[] =  $dat;
                          // $tanggal_spp = date("Y-m-d H:i:s", strtotime($dat->tanggal_spp));
                          // $verifikasi_spp_at = date("Y-m-d H:i:s", strtotime($dat->verifikasi_spp_at));
                          // $status_perubahan_at = date("Y-m-d H:i:s", strtotime($dat->status_perubahan_at));
                          $updated_at = date("Y-m-d H:i:s", strtotime($dat->updated_at));
                          // $created_at = date("Y-m-d H:i:s", strtotime($dat->created_at));
                          // $created_at = date("Y-m-d H:i:s", strtotime($dat->created_at));
                          $cek   = SPM::where('id_spm',$dat->id_spm)->first();
                          if(!$cek){
                              SPM::create([
                                'id_spm' => $dat->id_spm,
                                'nomor_spm' => $dat->nomor_spm,
                                'id_spp' => $dat->id_spp,
                                'nomor_spp' => $dat->nomor_spp,
                                'tahun' => $dat->tahun,
                                'id_daerah' => $dat->id_daerah,
                                'id_unit' => $dat->id_unit,
                                'id_skpd' => $dat->id_skpd,
                                'id_sub_skpd' => $dat->id_sub_skpd,
                                'kode_sub_skpd' => $dat->kode_sub_skpd,
                                'nama_sub_skpd' => $dat->nama_sub_skpd,
                                'nilai_spm' => $dat->nilai_spm,
                                'tanggal_spm' => $dat->tanggal_spm,
                                'keterangan_spm' => $dat->keterangan_spm,
                                'is_verifikasi_spm' => $dat->is_verifikasi_spm,
                                'verifikasi_spm_by' => $dat->verifikasi_spm_by,
                                'verifikasi_spm_at' => $dat->verifikasi_spm_at,
                                'keterangan_verifikasi_spm' => $dat->keterangan_verifikasi_spm,
                                'jenis_spm' => $dat->jenis_spm,
                                'jenis_ls_spm' => $dat->jenis_ls_spm,
                                'is_kunci_rekening_spm' => $dat->is_kunci_rekening_spm,
                                'is_sptjm_spm' => $dat->is_sptjm_spm,
                                'is_status_perubahan' => $dat->is_status_perubahan,
                                'status_perubahan_at' => $dat->status_perubahan_at,
                                'status_perubahan_by' => $dat->status_perubahan_by,
                                'id_jadwal' => $dat->id_jadwal,
                                'id_tahap' => $dat->id_tahap,
                                'status_tahap' => $dat->status_tahap,
                                'kode_tahap' => $dat->kode_tahap,
                                'created_by' => $dat->created_by,
                                'updated_by' => $dat->updated_by,
                                'deleted_at' => $dat->deleted_at,
                                'deleted_by' => $dat->deleted_by,
                                'bulan_gaji' => $dat->bulan_gaji,
                                'created_at' => $dat->created_at,
                                'updated_at' => $dat->updated_at,
                              ]);
                          }
                      }
                  }
                  $a++;
              } catch (Exception $e) {
                  return ['success' => false, 'message' => 'Error Get API', 'ex'=>$e ];
              }
          }

          return response()->json([
              'success' => true,
              'message' => 'Sukses Singkron Data',
              'data'  => [],
          ], 200);
    }

    static function SyncSPPD(){
          $token  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJTSVBEX0FVVEhfU0VSVklDRSIsInN1YiI6IjUwMS41MjciLCJleHAiOjE3MjY3NzUyODMsImlhdCI6MTcyNjU1OTI4MywidGFodW4iOjIwMjQsImlkX3VzZXIiOjUwMSwiaWRfZGFlcmFoIjo1MjcsImtvZGVfcHJvdmluc2kiOiIyMSIsImlkX3NrcGQiOjAsImlkX3JvbGUiOjksImlkX3BlZ2F3YWkiOjUwMSwic3ViX2RvbWFpbl9kYWVyYWgiOiJrZXB1bGF1YW5hbmFtYmFza2FiIn0.6t8w6706vE-Ep9d9O9Z-uzvp0hggZoEC2stUUE-bC3Q';

          $method = 'GET';
          $datas  = [];
          $a      = 1;
          // $a      = 760;
          while ($a <= 10000) {
              try {
                  $url    = '/pengeluaran/strict/sp2d/pembuatan/index?page='.$a;
                  $data = API::SIPD($token,$url,$method);
                  if(!$data){
                      break;
                  }
                  else{
                      foreach($data as $dat){
                          $datas =  $dat;
                          // $tanggal_spp = date("Y-m-d H:i:s", strtotime($dat->tanggal_spp));
                          // $verifikasi_spp_at = date("Y-m-d H:i:s", strtotime($dat->verifikasi_spp_at));
                          // $status_perubahan_at = date("Y-m-d H:i:s", strtotime($dat->status_perubahan_at));
                          // $updated_at = date("Y-m-d H:i:s", strtotime($dat->updated_at));
                          // $created_at = date("Y-m-d H:i:s", strtotime($dat->created_at));
                          // $created_at = date("Y-m-d H:i:s", strtotime($dat->created_at));
                          $cek   = SPPD::where('id_sp_2_d',$dat->id_sp_2_d)->first();
                          if(!$cek){
                              SPPD::create([
                                'id_sp_2_d' => $dat->id_sp_2_d,
                                'nomor_sp_2_d' => $dat->nomor_sp_2_d,
                                'id_spm' => $dat->id_spm,
                                'nomor_spm' => $dat->nomor_spm,
                                'tanggal_spm' => $dat->tanggal_spm,
                                'tahun' => $dat->tahun,
                                'id_daerah' => $dat->id_daerah,
                                'id_unit' => $dat->id_unit,
                                'id_skpd' => $dat->id_skpd,
                                'kode_skpd' => $dat->kode_skpd,
                                'nama_skpd' => $dat->nama_skpd,
                                'id_sub_skpd' => $dat->id_sub_skpd,
                                'kode_sub_skpd' => $dat->kode_sub_skpd,
                                'nama_sub_skpd' => $dat->nama_sub_skpd,
                                'nilai_sp_2_d' => $dat->nilai_sp_2_d,
                                'nilai_materai_sp_2_d' => $dat->nilai_materai_sp_2_d,
                                'tanggal_sp_2_d' => $dat->tanggal_sp_2_d,
                                'keterangan_sp_2_d' => $dat->keterangan_sp_2_d,
                                'is_verifikasi_sp_2_d' => $dat->is_verifikasi_sp_2_d,
                                'verifikasi_sp_2_d_by' => $dat->verifikasi_sp_2_d_by,
                                'verifikasi_sp_2_d_at' => $dat->verifikasi_sp_2_d_at,
                                'keterangan_verifikasi_sp_2_d' => $dat->keterangan_verifikasi_sp_2_d,
                                'is_transfer_sp_2_d' => $dat->is_transfer_sp_2_d,
                                'transfer_sp_2_d_by' => $dat->transfer_sp_2_d_by,
                                'transfer_sp_2_d_at' => $dat->transfer_sp_2_d_at,
                                'keterangan_transfer_sp_2_d' => $dat->keterangan_transfer_sp_2_d,
                                'jenis_sp_2_d' => $dat->jenis_sp_2_d,
                                'jenis_ls_sp_2_d' => $dat->jenis_ls_sp_2_d,
                                'is_kunci_rekening_sp_2_d' => $dat->is_kunci_rekening_sp_2_d,
                                'is_gaji' => $dat->is_gaji,
                                'jenis_gaji' => $dat->jenis_gaji,
                                'bulan_gaji' => $dat->bulan_gaji,
                                'tahun_gaji' => $dat->tahun_gaji,
                                'is_tpp' => $dat->is_tpp,
                                'bulan_tpp' => $dat->bulan_tpp,
                                'tahun_tpp' => $dat->tahun_tpp,
                                'is_pelimpahan' => $dat->is_pelimpahan,
                                'id_pegawai_bud_kbud' => $dat->id_pegawai_bud_kbud,
                                'nama_bud_kbud' => $dat->nama_bud_kbud,
                                'nip_bud_kbud' => $dat->nip_bud_kbud,
                                'id_rkud' => $dat->id_rkud,
                                'jenis_rkud' => $dat->jenis_rkud,
                                'no_rek_bp_bpp' => $dat->no_rek_bp_bpp,
                                'nama_rek_bp_bpp' => $dat->nama_rek_bp_bpp,
                                'id_bank' => $dat->id_bank,
                                'nama_bank' => $dat->nama_bank,
                                'id_sumber_dana' => $dat->id_sumber_dana,
                                'is_status_perubahan' => $dat->is_status_perubahan,
                                'status_perubahan_at' => $dat->status_perubahan_at,
                                'status_perubahan_by' => $dat->status_perubahan_by,
                                'status_aklap' => $dat->status_aklap,
                                'nomor_jurnal' => $dat->nomor_jurnal,
                                'jurnal_id' => $dat->jurnal_id,
                                'metode' => $dat->metode,
                                'id_jadwal' => $dat->id_jadwal,
                                'id_tahap' => $dat->id_tahap,
                                'status_tahap' => $dat->status_tahap,
                                'kode_tahap' => $dat->kode_tahap,
                                'created_by' => $dat->created_by,
                                'updated_by' => $dat->updated_by,
                                'deleted_by' => $dat->deleted_by,
                                'overbook_gagal' => $dat->overbook_gagal,
                                'overbook_expired' => $dat->overbook_expired,
                                'execution_time' => $dat->execution_time,
                                'created_at' => $dat->created_at,
                                'updated_at' => $dat->updated_at,
                              ]);
                          }
                      }
                  }
                  $a++;
              } catch (Exception $e) {
                  return ['success' => false, 'message' => 'Error Get API', 'ex'=>$e ];
              }
          }

          return response()->json([
              'success' => true,
              'message' => 'Sukses Singkron Data',
              'data'  => $datas,
          ], 200);
    }

    static function SyncSPPDCetak($id){
          $token  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJTSVBEX0FVVEhfU0VSVklDRSIsInN1YiI6IjUwMS41MjciLCJleHAiOjE3MjY3NzUyODMsImlhdCI6MTcyNjU1OTI4MywidGFodW4iOjIwMjQsImlkX3VzZXIiOjUwMSwiaWRfZGFlcmFoIjo1MjcsImtvZGVfcHJvdmluc2kiOiIyMSIsImlkX3NrcGQiOjAsImlkX3JvbGUiOjksImlkX3BlZ2F3YWkiOjUwMSwic3ViX2RvbWFpbl9kYWVyYWgiOiJrZXB1bGF1YW5hbmFtYmFza2FiIn0.6t8w6706vE-Ep9d9O9Z-uzvp0hggZoEC2stUUE-bC3Q';

          $method = 'POST';
          $datas  = [];
          $a      = 1;
          // $a      = 760;
          try {
              $url    = '/pengeluaran/strict/sp2d/pembuatan/cetak/'.$id;
              $data = API::SIPD($token,$url,$method);
              if($data){
                  $jenis = $data->jenis;
                  if($jenis == 'LS'){
                      $header  = $data->ls->header;
                      $cek  = DB::table('ta_sppd_cetak')->where('id_sp_2_d',$id)->first();
                      if($cek){
                          DB::table('ta_sppd_cetak')->where('id_sp_2_d',$id)->update([
                              'tahun' => $header->tahun,
                              'nomor_rekening' => $header->nomor_rekening,
                              'nama_bank' => $header->nama_bank,
                              'nomor_sp_2_d' => $header->nomor_sp_2_d,
                              'tanggal_sp_2_d' => $header->tanggal_sp_2_d,
                              'nama_skpd' => $header->nama_skpd,
                              'nama_sub_skpd' => $header->nama_sub_skpd,
                              'nama_pihak_ketiga' => $header->nama_pihak_ketiga,
                              'no_rek_pihak_ketiga' => $header->no_rek_pihak_ketiga,
                              'nama_rek_pihak_ketiga' => $header->nama_rek_pihak_ketiga,
                              'bank_pihak_ketiga' => $header->bank_pihak_ketiga,
                              'npwp_pihak_ketiga' => $header->npwp_pihak_ketiga,
                              'keterangan_sp2d' => $header->keterangan_sp2d,
                              'nilai_sp2d' => $header->nilai_sp2d,
                              'cabang_bank' => $header->cabang_bank,
                              'nomor_spm' => $header->nomor_spm,
                              'tanggal_spm' => $header->tanggal_spm,
                              'nama_ibu_kota' => $header->nama_ibu_kota,
                              'nama_bud_kbud' => $header->nama_bud_kbud,
                              'nip_bud_kbud' => $header->nip_bud_kbud,
                              'jabatan_bud_kbud' => $header->jabatan_bud_kbud,
                          ]);
                      }else{
                          DB::table('ta_sppd_cetak')->insert([
                              'id_sp_2_d'  => $id,
                              'tahun' => $header->tahun,
                              'nomor_rekening' => $header->nomor_rekening,
                              'nama_bank' => $header->nama_bank,
                              'nomor_sp_2_d' => $header->nomor_sp_2_d,
                              'tanggal_sp_2_d' => $header->tanggal_sp_2_d,
                              'nama_skpd' => $header->nama_skpd,
                              'nama_sub_skpd' => $header->nama_sub_skpd,
                              'nama_pihak_ketiga' => $header->nama_pihak_ketiga,
                              'no_rek_pihak_ketiga' => $header->no_rek_pihak_ketiga,
                              'nama_rek_pihak_ketiga' => $header->nama_rek_pihak_ketiga,
                              'bank_pihak_ketiga' => $header->bank_pihak_ketiga,
                              'npwp_pihak_ketiga' => $header->npwp_pihak_ketiga,
                              'keterangan_sp2d' => $header->keterangan_sp2d,
                              'nilai_sp2d' => $header->nilai_sp2d,
                              'cabang_bank' => $header->cabang_bank,
                              'nomor_spm' => $header->nomor_spm,
                              'tanggal_spm' => $header->tanggal_spm,
                              'nama_ibu_kota' => $header->nama_ibu_kota,
                              'nama_bud_kbud' => $header->nama_bud_kbud,
                              'nip_bud_kbud' => $header->nip_bud_kbud,
                              'jabatan_bud_kbud' => $header->jabatan_bud_kbud,
                          ]);
                      }
                      $detail  = $data->ls->detail_belanja;
                      DB::table('ta_sppd_rekening')->where('id_sp_2_d',$id)->delete();
                      foreach($detail as $dat){
                          if($dat->kode_rekening != ''){
                              DB::table('ta_sppd_rekening')->insert([
                                  'id_sp_2_d'  => $id,
                                  'kode_rekening' => $dat->kode_rekening,
                                  'uraian' => $dat->uraian,
                                  'total_anggaran' => $dat->total_anggaran,
                                  'jumlah' => $dat->jumlah,
                              ]);
                          }
                      }
                      $potongan  = $data->ls->pajak_potongan;
                      DB::table('ta_sppd_potongan')->where('id_sp_2_d',$id)->delete();
                      if($potongan){
                          foreach($potongan as $dat){
                              DB::table('ta_sppd_potongan')->insert([
                                  'id_sp_2_d'  => $id,
                                  'id_pajak_potongan' => $dat->id_pajak_potongan,
                                  'nama_pajak_potongan' => $dat->nama_pajak_potongan,
                                  'kode_sinergi' => $dat->kode_sinergi,
                                  'nama_sinergi' => $dat->nama_sinergi,
                                  'id_billing' => $dat->id_billing,
                                  'nilai_sp2d_pajak_potongan' => $dat->nilai_sp2d_pajak_potongan,
                              ]);
                          }
                      }
                  }
              }
          } catch (Exception $e) {
              return ['success' => false, 'message' => 'Error Get API', 'ex'=>$e ];
          }

          return response()->json([
              'success' => true,
              'message' => 'Sukses Singkron Data',
              'data'  => $data,
          ], 200);
    }

    static function SyncSPPCetak($id){
          $token  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJTSVBEX0FVVEhfU0VSVklDRSIsInN1YiI6IjUwMS41MjciLCJleHAiOjE3MjY3NzUyODMsImlhdCI6MTcyNjU1OTI4MywidGFodW4iOjIwMjQsImlkX3VzZXIiOjUwMSwiaWRfZGFlcmFoIjo1MjcsImtvZGVfcHJvdmluc2kiOiIyMSIsImlkX3NrcGQiOjAsImlkX3JvbGUiOjksImlkX3BlZ2F3YWkiOjUwMSwic3ViX2RvbWFpbl9kYWVyYWgiOiJrZXB1bGF1YW5hbmFtYmFza2FiIn0.6t8w6706vE-Ep9d9O9Z-uzvp0hggZoEC2stUUE-bC3Q';

          $method = 'GET';
          $datas  = [];
          $a      = 1;
          // $a      = 760;
          try {
              $url    = '/pengeluaran/strict/spp/pembuatan/cetak/'.$id;
              $data = API::SIPD($token,$url,$method);
              if($data){
                  $jenis = $data->jenis;
                  if($jenis == 'LS'){
                      $header  = $data->ls->header;
                      $cek  = DB::table('ta_spp_cetak')->where('id_spp',$id)->first();
                      if($cek){
                          DB::table('ta_spp_cetak')->where('id_spp',$id)->update([
                              'tahun' => $header->tahun,
                              'nomor_transaksi' => $header->nomor_transaksi,
                              'tanggal_transaksi' => $header->tanggal_transaksi,
                              'nama_pa_kpa' => $header->nama_pa_kpa,
                              'nip_pa_kpa' => $header->nip_pa_kpa,
                              'nama_skpd' => $header->nama_skpd,
                              'nama_sub_skpd' => $header->nama_sub_skpd,
                              'jabatan_pa_kpa' => $header->jabatan_pa_kpa,
                              'nama_pptk' => $header->nama_pptk,
                              'nip_pptk' => $header->nip_pptk,
                              'no_rek_bp_bpp' => $header->no_rek_bp_bpp,
                              'nama_rek_bp_bpp' => $header->nama_rek_bp_bpp,
                              'bank_bp_bpp' => $header->bank_bp_bpp,
                              'npwp_bp_bpp' => $header->npwp_bp_bpp,
                              'nama_bp_bpp' => $header->nama_bp_bpp,
                              'nip_bp_bpp' => $header->nip_bp_bpp,
                              'jabatan_bp_bpp' => $header->jabatan_bp_bpp,
                              'keterangan' => $header->keterangan,
                          ]);
                      }else{
                          DB::table('ta_spp_cetak')->insert([
                              'id_spp'  => $id,
                              'tahun' => $header->tahun,
                              'nomor_transaksi' => $header->nomor_transaksi,
                              'tanggal_transaksi' => $header->tanggal_transaksi,
                              'nama_pa_kpa' => $header->nama_pa_kpa,
                              'nip_pa_kpa' => $header->nip_pa_kpa,
                              'nama_skpd' => $header->nama_skpd,
                              'nama_sub_skpd' => $header->nama_sub_skpd,
                              'jabatan_pa_kpa' => $header->jabatan_pa_kpa,
                              'nama_pptk' => $header->nama_pptk,
                              'nip_pptk' => $header->nip_pptk,
                              'no_rek_bp_bpp' => $header->no_rek_bp_bpp,
                              'nama_rek_bp_bpp' => $header->nama_rek_bp_bpp,
                              'bank_bp_bpp' => $header->bank_bp_bpp,
                              'npwp_bp_bpp' => $header->npwp_bp_bpp,
                              'nama_bp_bpp' => $header->nama_bp_bpp,
                              'nip_bp_bpp' => $header->nip_bp_bpp,
                              'jabatan_bp_bpp' => $header->jabatan_bp_bpp,
                              'keterangan' => $header->keterangan,
                          ]);
                      }
                      $detail  = $data->ls->detail;
                      foreach($detail as $dat){
                          $cek  = DB::table('ta_spp_rekening')->where('id_spp',$id)->where('kode_rekening',$dat->kode_rekening)->first();
                          if($cek){
                              DB::table('ta_spp_rekening')->where('id',$cek->id)->update([
                                  'kode_rekening' => $dat->kode_rekening,
                                  'uraian' => $dat->uraian,
                                  'jumlah' => $dat->jumlah,
                              ]);
                          }else{
                              if($dat->kode_rekening != ''){
                                  DB::table('ta_spp_rekening')->insert([
                                      'id_spp'  => $id,
                                      'kode_rekening' => $dat->kode_rekening,
                                      'uraian' => $dat->uraian,
                                      'jumlah' => $dat->jumlah,
                                  ]);
                              }
                          }
                      }
                  }
                  $data  = $data->ls->detail;
              }
          } catch (Exception $e) {
              return ['success' => false, 'message' => 'Error Get API', 'ex'=>$e ];
          }

          return response()->json([
              'success' => true,
              'message' => 'Sukses Singkron Data',
              'data'  => $data,
          ], 200);
    }

    static function SyncGajiPegawai($req){
          $token  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJTSVBEX0FVVEhfU0VSVklDRSIsInN1YiI6IjUwMS41MjciLCJleHAiOjE3MjY3NzUyODMsImlhdCI6MTcyNjU1OTI4MywidGFodW4iOjIwMjQsImlkX3VzZXIiOjUwMSwiaWRfZGFlcmFoIjo1MjcsImtvZGVfcHJvdmluc2kiOiIyMSIsImlkX3NrcGQiOjAsImlkX3JvbGUiOjksImlkX3BlZ2F3YWkiOjUwMSwic3ViX2RvbWFpbl9kYWVyYWgiOiJrZXB1bGF1YW5hbmFtYmFza2FiIn0.6t8w6706vE-Ep9d9O9Z-uzvp0hggZoEC2stUUE-bC3Q';
          // return $req->all();
          $method = 'GET';
          $datas  = [];
          $a      = 1;
          // $a      = 760;
          $exp    = explode('-',$req->tanggal);
          if(sizeOf($exp) != 3) return null;
          $tahun  = $exp[0];
          $bulan  = (int)$exp[1];
          try {
              $url    = '/pengeluaran/strict/gaji-pegawai?id_skpd='.$req->id_skpd.'&page=1&limit=1000&bulan='.$bulan.'&jenis_pegawai='.$req->jenis_pegawai;
              $data = API::SIPD($token,$url,$method);
              if($data){
                  foreach($data as $dat){
                      $cek = GAJI::where('id_gaji_pegawai',$dat->id_gaji_pegawai)->first();
                      if($cek){
                          GAJI::where('id_gaji_pegawai',$dat->id_gaji_pegawai)->update([
                              'id_daerah' => $dat->id_daerah,
                              'id_skpd' => $dat->id_skpd,
                              'bulan_gaji' => $dat->bulan_gaji,
                              'tahun_gaji' => $dat->tahun_gaji,
                              'jenis_gaji' => $dat->jenis_gaji,
                              'nip_pegawai' => $dat->nip_pegawai,
                              'nama_pegawai' => $dat->nama_pegawai,
                              'nik_pegawai' => $dat->nik_pegawai,
                              'npwp_pegawai' => $dat->npwp_pegawai,
                              'tanggal_lahir_pegawai' => $dat->tanggal_lahir_pegawai,
                              'id_tipe_jabatan' => $dat->id_tipe_jabatan,
                              'nama_jabatan' => $dat->nama_jabatan,
                              'eselon' => $dat->eselon,
                              'pppk_pns' => $dat->pppk_pns,
                              'golongan' => $dat->golongan,
                              'mkg' => $dat->mkg,
                              'alamat' => $dat->alamat,
                              'status_pernikahan' => $dat->status_pernikahan,
                              'jumlah_istri_suami' => $dat->jumlah_istri_suami,
                              'jumlah_anak' => $dat->jumlah_anak,
                              'jumlah_tanggungan' => $dat->jumlah_tanggungan,
                              'is_pasangan_pns' => $dat->is_pasangan_pns,
                              'nip_pasangan' => $dat->nip_pasangan,
                              'kode_bank' => $dat->kode_bank,
                              'nama_bank' => $dat->nama_bank,
                              'nomor_rekening_bank_pegawai' => $dat->nomor_rekening_bank_pegawai,
                              'belanja_gaji_pokok' => $dat->belanja_gaji_pokok,
                              'perhitungan_suami_istri' => $dat->perhitungan_suami_istri,
                              'perhitungan_anak' => $dat->perhitungan_anak,
                              'belanja_tunjangan_keluarga' => $dat->belanja_tunjangan_keluarga,
                              'belanja_tunjangan_jabatan' => $dat->belanja_tunjangan_jabatan,
                              'belanja_tunjangan_fungsional' => $dat->belanja_tunjangan_fungsional,
                              'belanja_tunjangan_fungsional_umum' => $dat->belanja_tunjangan_fungsional_umum,
                              'belanja_tunjangan_beras' => $dat->belanja_tunjangan_beras,
                              'belanja_tunjangan_pph' => $dat->belanja_tunjangan_pph,
                              'belanja_pembulatan_gaji' => $dat->belanja_pembulatan_gaji,
                              'belanja_iuran_jaminan_kesehatan' => $dat->belanja_iuran_jaminan_kesehatan,
                              'belanja_iuran_jaminan_kecelakaan_kerja' => $dat->belanja_iuran_jaminan_kecelakaan_kerja,
                              'belanja_iuran_jaminan_kematian' => $dat->belanja_iuran_jaminan_kematian,
                              'belanja_iuran_simpanan_tapera' => $dat->belanja_iuran_simpanan_tapera,
                              'belanja_iuran_pensiun' => $dat->belanja_iuran_pensiun,
                              'tunjangan_khusus_papua' => $dat->tunjangan_khusus_papua,
                              'tunjangan_jaminan_hari_tua' => $dat->tunjangan_jaminan_hari_tua,
                              'potongan_iwp' => $dat->potongan_iwp,
                              'potongan_pph_21' => $dat->potongan_pph_21,
                              'zakat' => $dat->zakat,
                              'bulog' => $dat->bulog,
                              'jumlah_gaji_tunjangan' => $dat->jumlah_gaji_tunjangan,
                              'jumlah_potongan' => $dat->jumlah_potongan,
                              'jumlah_ditransfer' => $dat->jumlah_ditransfer,
                              'id_transaksi' => $dat->id_transaksi,
                              'status_transaksi' => $dat->status_transaksi,
                              'transaksi_note' => $dat->transaksi_note,
                              'bpd_code' => $dat->bpd_code,
                              'nomor_transaksi_bank' => $dat->nomor_transaksi_bank,
                              'nama_rekening_bank' => $dat->nama_rekening_bank,
                              'is_manual' => $dat->is_manual,
                              'created_at' => $dat->created_at,
                              'created_by' => $dat->created_by,
                              'updated_at' => $dat->updated_at,
                              'updated_by' => $dat->updated_by,
                              'deleted_at' => $dat->deleted_at,
                              'deleted_by' => $dat->deleted_by,
                          ]);

                      }else{
                          GAJI::insert([
                              'id_gaji_pegawai' => $dat->id_gaji_pegawai,
                              'id_daerah' => $dat->id_daerah,
                              'id_skpd' => $dat->id_skpd,
                              'bulan_gaji' => $dat->bulan_gaji,
                              'tahun_gaji' => $dat->tahun_gaji,
                              'jenis_gaji' => $dat->jenis_gaji,
                              'nip_pegawai' => $dat->nip_pegawai,
                              'nama_pegawai' => $dat->nama_pegawai,
                              'nik_pegawai' => $dat->nik_pegawai,
                              'npwp_pegawai' => $dat->npwp_pegawai,
                              'tanggal_lahir_pegawai' => $dat->tanggal_lahir_pegawai,
                              'id_tipe_jabatan' => $dat->id_tipe_jabatan,
                              'nama_jabatan' => $dat->nama_jabatan,
                              'eselon' => $dat->eselon,
                              'pppk_pns' => $dat->pppk_pns,
                              'golongan' => $dat->golongan,
                              'mkg' => $dat->mkg,
                              'alamat' => $dat->alamat,
                              'status_pernikahan' => $dat->status_pernikahan,
                              'jumlah_istri_suami' => $dat->jumlah_istri_suami,
                              'jumlah_anak' => $dat->jumlah_anak,
                              'jumlah_tanggungan' => $dat->jumlah_tanggungan,
                              'is_pasangan_pns' => $dat->is_pasangan_pns,
                              'nip_pasangan' => $dat->nip_pasangan,
                              'kode_bank' => $dat->kode_bank,
                              'nama_bank' => $dat->nama_bank,
                              'nomor_rekening_bank_pegawai' => $dat->nomor_rekening_bank_pegawai,
                              'belanja_gaji_pokok' => $dat->belanja_gaji_pokok,
                              'perhitungan_suami_istri' => $dat->perhitungan_suami_istri,
                              'perhitungan_anak' => $dat->perhitungan_anak,
                              'belanja_tunjangan_keluarga' => $dat->belanja_tunjangan_keluarga,
                              'belanja_tunjangan_jabatan' => $dat->belanja_tunjangan_jabatan,
                              'belanja_tunjangan_fungsional' => $dat->belanja_tunjangan_fungsional,
                              'belanja_tunjangan_fungsional_umum' => $dat->belanja_tunjangan_fungsional_umum,
                              'belanja_tunjangan_beras' => $dat->belanja_tunjangan_beras,
                              'belanja_tunjangan_pph' => $dat->belanja_tunjangan_pph,
                              'belanja_pembulatan_gaji' => $dat->belanja_pembulatan_gaji,
                              'belanja_iuran_jaminan_kesehatan' => $dat->belanja_iuran_jaminan_kesehatan,
                              'belanja_iuran_jaminan_kecelakaan_kerja' => $dat->belanja_iuran_jaminan_kecelakaan_kerja,
                              'belanja_iuran_jaminan_kematian' => $dat->belanja_iuran_jaminan_kematian,
                              'belanja_iuran_simpanan_tapera' => $dat->belanja_iuran_simpanan_tapera,
                              'belanja_iuran_pensiun' => $dat->belanja_iuran_pensiun,
                              'tunjangan_khusus_papua' => $dat->tunjangan_khusus_papua,
                              'tunjangan_jaminan_hari_tua' => $dat->tunjangan_jaminan_hari_tua,
                              'potongan_iwp' => $dat->potongan_iwp,
                              'potongan_pph_21' => $dat->potongan_pph_21,
                              'zakat' => $dat->zakat,
                              'bulog' => $dat->bulog,
                              'jumlah_gaji_tunjangan' => $dat->jumlah_gaji_tunjangan,
                              'jumlah_potongan' => $dat->jumlah_potongan,
                              'jumlah_ditransfer' => $dat->jumlah_ditransfer,
                              'id_transaksi' => $dat->id_transaksi,
                              'status_transaksi' => $dat->status_transaksi,
                              'transaksi_note' => $dat->transaksi_note,
                              'bpd_code' => $dat->bpd_code,
                              'nomor_transaksi_bank' => $dat->nomor_transaksi_bank,
                              'nama_rekening_bank' => $dat->nama_rekening_bank,
                              'is_manual' => $dat->is_manual,
                              'created_at' => $dat->created_at,
                              'created_by' => $dat->created_by,
                              'updated_at' => $dat->updated_at,
                              'updated_by' => $dat->updated_by,
                              'deleted_at' => $dat->deleted_at,
                              'deleted_by' => $dat->deleted_by,
                          ]);
                      }
                  }
              }
          } catch (Exception $e) {
              return ['success' => false, 'message' => 'Error Get API', 'ex'=>$e ];
          }

          return response()->json([
              'success' => true,
              'message' => 'Sukses Singkron Data',
              'data'  => $data,
          ], 200);
    }

    static function SyncDPA($req){
          $token  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJTSVBEX0FVVEhfU0VSVklDRSIsInN1YiI6IjUwMS41MjciLCJleHAiOjE3MjY3NzUyODMsImlhdCI6MTcyNjU1OTI4MywidGFodW4iOjIwMjQsImlkX3VzZXIiOjUwMSwiaWRfZGFlcmFoIjo1MjcsImtvZGVfcHJvdmluc2kiOiIyMSIsImlkX3NrcGQiOjAsImlkX3JvbGUiOjksImlkX3BlZ2F3YWkiOjUwMSwic3ViX2RvbWFpbl9kYWVyYWgiOiJrZXB1bGF1YW5hbmFtYmFza2FiIn0.6t8w6706vE-Ep9d9O9Z-uzvp0hggZoEC2stUUE-bC3Q';
          // return $req->all();
          $method = 'GET';
          $datas  = [];
          $a      = 1;
          try {
              $url    = '/referensi/strict/dpa/penarikan/belanja/skpd/'.$req->id_skpd;
              $data = API::SIPD($token,$url,$method);
              if($data){
                  $data = $data->items;
                  if(sizeOf($data)){
                      foreach($data as $dat){
                          $cek = DPA::where('id_skpd',$dat->id_skpd)->where('id_sub_skpd',$dat->id_sub_skpd)->where('id_sub_giat',$dat->id_sub_giat)->first();
                          if($cek){
                              DPA::where('id',$cek->id)->update([
                                  'id_daerah' => $dat->id_daerah,
                                  'tahun' => $dat->tahun,
                                  'id_unit' => $dat->id_unit,
                                  'id_skpd' => $dat->id_skpd,
                                  'kode_skpd' => $dat->kode_skpd,
                                  'nama_skpd' => $dat->nama_skpd,
                                  'id_sub_skpd' => $dat->id_sub_skpd,
                                  'kode_sub_skpd' => $dat->kode_sub_skpd,
                                  'nama_sub_skpd' => $dat->nama_sub_skpd,
                                  'id_urusan' => $dat->id_urusan,
                                  'id_bidang_urusan' => $dat->id_bidang_urusan,
                                  'kode_bidang_urusan' => $dat->kode_bidang_urusan,
                                  'nama_bidang_urusan' => $dat->nama_bidang_urusan,
                                  'id_program' => $dat->id_program,
                                  'kode_program' => $dat->kode_program,
                                  'nama_program' => $dat->nama_program,
                                  'id_giat' => $dat->id_giat,
                                  'kode_giat' => $dat->kode_giat,
                                  'nama_giat' => $dat->nama_giat,
                                  'id_sub_giat' => $dat->id_sub_giat,
                                  'kode_sub_giat' => $dat->kode_sub_giat,
                                  'nama_sub_giat' => $dat->nama_sub_giat,
                                  'nilai' => $dat->nilai,
                                  'nilai_rak' => $dat->nilai_rak,
                                  'status' => $dat->status,
                                  'rak_belum_sesuai' => $dat->rak_belum_sesuai,

                              ]);
                          }else{
                              DPA::insert([
                                  'id_daerah' => $dat->id_daerah,
                                  'tahun' => $dat->tahun,
                                  'id_unit' => $dat->id_unit,
                                  'id_skpd' => $dat->id_skpd,
                                  'kode_skpd' => $dat->kode_skpd,
                                  'nama_skpd' => $dat->nama_skpd,
                                  'id_sub_skpd' => $dat->id_sub_skpd,
                                  'kode_sub_skpd' => $dat->kode_sub_skpd,
                                  'nama_sub_skpd' => $dat->nama_sub_skpd,
                                  'id_urusan' => $dat->id_urusan,
                                  'id_bidang_urusan' => $dat->id_bidang_urusan,
                                  'kode_bidang_urusan' => $dat->kode_bidang_urusan,
                                  'nama_bidang_urusan' => $dat->nama_bidang_urusan,
                                  'id_program' => $dat->id_program,
                                  'kode_program' => $dat->kode_program,
                                  'nama_program' => $dat->nama_program,
                                  'id_giat' => $dat->id_giat,
                                  'kode_giat' => $dat->kode_giat,
                                  'nama_giat' => $dat->nama_giat,
                                  'id_sub_giat' => $dat->id_sub_giat,
                                  'kode_sub_giat' => $dat->kode_sub_giat,
                                  'nama_sub_giat' => $dat->nama_sub_giat,
                                  'nilai' => $dat->nilai,
                                  'nilai_rak' => $dat->nilai_rak,
                                  'status' => $dat->status,
                                  'rak_belum_sesuai' => $dat->rak_belum_sesuai,
                              ]);
                          }
                      }
                  }
              }
          } catch (Exception $e) {
              return ['success' => false, 'message' => 'Error Get API', 'ex'=>$e ];
          }

          return response()->json([
              'success' => true,
              'message' => 'Sukses Singkron Data',
              'payload' => $req->all(),
              'data'  => $data,
          ], 200);
    }

    static function SyncSubGiat($req){
          $token  = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJTSVBEX0FVVEhfU0VSVklDRSIsInN1YiI6IjUwMS41MjciLCJleHAiOjE3MjY3NzUyODMsImlhdCI6MTcyNjU1OTI4MywidGFodW4iOjIwMjQsImlkX3VzZXIiOjUwMSwiaWRfZGFlcmFoIjo1MjcsImtvZGVfcHJvdmluc2kiOiIyMSIsImlkX3NrcGQiOjAsImlkX3JvbGUiOjksImlkX3BlZ2F3YWkiOjUwMSwic3ViX2RvbWFpbl9kYWVyYWgiOiJrZXB1bGF1YW5hbmFtYmFza2FiIn0.6t8w6706vE-Ep9d9O9Z-uzvp0hggZoEC2stUUE-bC3Q';
          $method = 'GET';
          $datas  = [];
          $a      = 1;
          try {
              // "https://service.sipd.kemendagri.go.id/referensi/strict/dpa/penarikan/belanja/sub-giat?id_unit=144&id_skpd=144&id_sub_skpd=144&id_urusan=11&id_bidang_urusan=201&id_program=1000&id_giat=8001&id_sub_giat=17049"
              $url    = '/referensi/strict/dpa/penarikan/belanja/sub-giat?id_unit=144&id_skpd=144&id_sub_skpd=144&id_urusan=11&id_bidang_urusan=201&id_program=1000&id_giat=8001&id_sub_giat=17049';
              $data = API::SIPD($token,$url,$method);
              if($data){
                  // $data = $data->items;
                  if(sizeOf($data)){
                      foreach($data as $dat){
                          $cek = DPA_REKENING::where('id_skpd',$dat->id_skpd)->where('id_sub_skpd',$dat->id_sub_skpd)->where('id_sub_giat',$dat->id_sub_giat)->where('id_akun',$dat->id_akun)->first();
                          if($cek){
                              DPA_REKENING::where('id',$cek->id)->update([
                                  'id_daerah' => $dat->id_daerah,
                                  'tahun' => $dat->tahun,
                                  'id_unit' => $dat->id_unit,
                                  'id_skpd' => $dat->id_skpd,
                                  'kode_skpd' => $dat->kode_skpd,
                                  'nama_skpd' => $dat->nama_skpd,
                                  'id_sub_skpd' => $dat->id_sub_skpd,
                                  'kode_sub_skpd' => $dat->kode_sub_skpd,
                                  'nama_sub_skpd' => $dat->nama_sub_skpd,
                                  'id_urusan' => $dat->id_urusan,
                                  'id_bidang_urusan' => $dat->id_bidang_urusan,
                                  'kode_bidang_urusan' => $dat->kode_bidang_urusan,
                                  'nama_bidang_urusan' => $dat->nama_bidang_urusan,
                                  'id_program' => $dat->id_program,
                                  'kode_program' => $dat->kode_program,
                                  'nama_program' => $dat->nama_program,
                                  'id_giat' => $dat->id_giat,
                                  'kode_giat' => $dat->kode_giat,
                                  'nama_giat' => $dat->nama_giat,
                                  'id_sub_giat' => $dat->id_sub_giat,
                                  'kode_sub_giat' => $dat->kode_sub_giat,
                                  'nama_sub_giat' => $dat->nama_sub_giat,
                                  'id_akun' => $dat->id_akun,
                                  'kode_akun' => $dat->kode_akun,
                                  'nama_akun' => $dat->nama_akun,
                                  'nilai' => $dat->nilai,
                                  'nilai_rak' => $dat->nilai_rak,
                                  'id_rak_belanja' => $dat->id_rak_belanja,
                              ]);
                          }else{
                              DPA_REKENING::insert([
                                  'id_daerah' => $dat->id_daerah,
                                  'tahun' => $dat->tahun,
                                  'id_unit' => $dat->id_unit,
                                  'id_skpd' => $dat->id_skpd,
                                  'kode_skpd' => $dat->kode_skpd,
                                  'nama_skpd' => $dat->nama_skpd,
                                  'id_sub_skpd' => $dat->id_sub_skpd,
                                  'kode_sub_skpd' => $dat->kode_sub_skpd,
                                  'nama_sub_skpd' => $dat->nama_sub_skpd,
                                  'id_urusan' => $dat->id_urusan,
                                  'id_bidang_urusan' => $dat->id_bidang_urusan,
                                  'kode_bidang_urusan' => $dat->kode_bidang_urusan,
                                  'nama_bidang_urusan' => $dat->nama_bidang_urusan,
                                  'id_program' => $dat->id_program,
                                  'kode_program' => $dat->kode_program,
                                  'nama_program' => $dat->nama_program,
                                  'id_giat' => $dat->id_giat,
                                  'kode_giat' => $dat->kode_giat,
                                  'nama_giat' => $dat->nama_giat,
                                  'id_sub_giat' => $dat->id_sub_giat,
                                  'kode_sub_giat' => $dat->kode_sub_giat,
                                  'nama_sub_giat' => $dat->nama_sub_giat,
                                  'id_akun' => $dat->id_akun,
                                  'kode_akun' => $dat->kode_akun,
                                  'nama_akun' => $dat->nama_akun,
                                  'nilai' => $dat->nilai,
                                  'nilai_rak' => $dat->nilai_rak,
                                  'id_rak_belanja' => $dat->id_rak_belanja,
                              ]);
                          }
                      }
                  }
              }
          } catch (Exception $e) {
              return ['success' => false, 'message' => 'Error Get API', 'ex'=>$e ];
          }

          return response()->json([
              'success' => true,
              'message' => 'Sukses Singkron Data',
              'payload' => $req->all(),
              'data'  => $data,
          ], 200);
    }




}
