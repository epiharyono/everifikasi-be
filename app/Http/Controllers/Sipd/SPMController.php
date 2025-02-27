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

class SPMController extends Controller
{

    static function SingkronSPM($req){
        $message = 'Sing SingSP2DDetail';
        $header = '';
        $data = $req->data;
        $data = json_decode($data, true);

        try {
            if(sizeOf($data)){
              foreach($data as $dat){
                $id   = $dat['id_spm'];
                $cek   = SPM::where('id_spm',$dat['id_spm'],)->first();
                if(!$cek){
                    SPM::create([
                      'id_spm' => $dat['id_spm'],
                      'nomor_spm' => $dat['nomor_spm'],
                      'id_spp' => $dat['id_spp'],
                      'nomor_spp' => $dat['nomor_spp'],
                      'tahun' => $dat['tahun'],
                      'id_daerah' => $dat['id_daerah'],
                      'id_unit' => $dat['id_unit'],
                      'id_skpd' => $dat['id_skpd'],
                      'id_sub_skpd' => $dat['id_sub_skpd'],
                      'kode_sub_skpd' => $dat['kode_sub_skpd'],
                      'nama_sub_skpd' => $dat['nama_sub_skpd'],
                      'nilai_spm' => $dat['nilai_spm'],
                      'tanggal_spm' => Carbon::parse($dat['tanggal_spm'])->toDateTimeString(),
                      'keterangan_spm' => $dat['keterangan_spm'],
                      'is_verifikasi_spm' => $dat['is_verifikasi_spm'],
                      'verifikasi_spm_by' => $dat['verifikasi_spm_by'],
                      'verifikasi_spm_at' => Carbon::parse($dat['verifikasi_spm_at'])->toDateTimeString(),
                      'keterangan_verifikasi_spm' => $dat['keterangan_verifikasi_spm'],
                      'jenis_spm' => $dat['jenis_spm'],
                      'jenis_ls_spm' => $dat['jenis_ls_spm'],
                      'is_kunci_rekening_spm' => $dat['is_kunci_rekening_spm'],
                      'is_sptjm_spm' => $dat['is_sptjm_spm'],
                      'is_status_perubahan' => $dat['is_status_perubahan'],
                      'status_perubahan_at' => Carbon::parse($dat['status_perubahan_at'])->toDateTimeString(),
                      'status_perubahan_by' => $dat['status_perubahan_by'],
                      'id_jadwal' => $dat['id_jadwal'],
                      'id_tahap' => $dat['id_tahap'],
                      'status_tahap' => $dat['status_tahap'],
                      'kode_tahap' => $dat['kode_tahap'],
                      'created_by' => $dat['created_by'],
                      'updated_by' => $dat['updated_by'],
                      'deleted_at' => Carbon::parse($dat['deleted_at'])->toDateTimeString(),
                      'deleted_by' => $dat['deleted_by'],
                      'bulan_gaji' => $dat['bulan_gaji'],
                      'created_at' => Carbon::parse($dat['created_at'])->toDateTimeString(),
                      'updated_at' => Carbon::parse($dat['updated_at'])->toDateTimeString(),
                    ]);
                }else{
                    SPM::where('id_spm',$cek->id_spm)->where('asis_final_bend',0)->update([
                      'nilai_spm' => $dat['nilai_spm'],
                      'tanggal_spm' => Carbon::parse($dat['tanggal_spm'])->toDateTimeString(),
                      'keterangan_spm' => $dat['keterangan_spm'],
                      'created_at' => Carbon::parse($dat['created_at'])->toDateTimeString(),
                      'updated_at' => Carbon::parse($dat['updated_at'])->toDateTimeString(),
                    ]);
                }


                $act  = 'spp_cetak';
                $act_id = $dat['id_spp'];
                $id_skpd = $dat['id_skpd'];
                $jenis  = '';
                $search = '';
                self::InsertAgenSing($act,$act_id,$id_skpd,$jenis,$search);
                $act  = 'spp_search';
                $search = $dat['nomor_spp'];
                self::InsertAgenSing($act,$act_id,$id_skpd,$jenis,$search);
                $act  = 'spm_cetak';
                $act_id = $dat['id_spm'];
                self::InsertAgenSing($act,$act_id,$id_skpd,$jenis,$search);


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

    static function SingkronSPMCetak($req){
        $message = ''; $data = ''; $pajak = ''; $potongan = '';
        $data = $req->data;
        $data = json_decode($data, true);
        $id   =  $req->id_spm;
        $jenis = $req->tipe;



        // return response()->json([
        //     'success' => false,
        //     'status'  => 'success',
        //     'message' => $message,
        //     'data'  => $data,
        //     'payload' => $req->all(),
        // ], 200);

        if(sizeOf($data)){

          if(isset($data['header'])){
              $header  = $data['header'];
              $cek  = DB::table('ta_spm_cetak')->where('id_spm',$id)->first();
              if(!$cek){
                  DB::table('ta_spm_cetak')->insert([
                      'id_spm'  => $id,
                      'nama_daerah' => $header['nama_daerah'],
                      'tahun' => $header['tahun'],
                      'nomor_spm' => $header['nomor_spm'],
                      'tanggal_spm' => Carbon::parse($header['tanggal_spm'])->toDateTimeString(),
                      'nama_skpd' => $header['nama_skpd'],
                      'nama_sub_skpd' => $header['nama_sub_skpd'],
                      'nama_pihak_ketiga' => $header['nama_pihak_ketiga'],
                      'no_rek_pihak_ketiga' => $header['no_rek_pihak_ketiga'],
                      'nama_rek_pihak_ketiga' => $header['nama_rek_pihak_ketiga'],
                      'bank_pihak_ketiga' => $header['bank_pihak_ketiga'],
                      'npwp_pihak_ketiga' => $header['npwp_pihak_ketiga'],
                      'keterangan_spm' => $header['keterangan_spm'],
                      'nilai_spm' => $header['nilai_spm'],
                      'nomor_spp' => $header['nomor_spp'],
                      'tanggal_spp' => Carbon::parse($header['tanggal_spp'])->toDateTimeString(),
                      'nama_ibukota' => $header['nama_ibukota'],
                      'cabang_bank' => $header['cabang_bank'],
                      'nama_pa_kpa' => $header['nama_pa_kpa'],
                      'nip_pa_kpa' => $header['nip_pa_kpa'],
                      'jabatan_pa_kpa' => $header['jabatan_pa_kpa'],
                  ]);

                  $pajak  = $data['pajak_potongan'];
                  if($pajak){
                      if(sizeOf($pajak)){
                          foreach($pajak as $dat){
                              $cek  = DB::table('ta_spm_pajak_potongan')->where('id_spm',$id)->where('id_pajak_potongan',$dat['id_pajak_potongan'])->first();
                              if(!$cek){
                                  DB::table('ta_spm_pajak_potongan')->insert([
                                      'id_spm'  => $id,
                                      'id_pajak_potongan' => $dat['id_pajak_potongan'],
                                      'nama_pajak_potongan' => $dat['nama_pajak_potongan'],
                                      'id_billing' => $dat['id_billing'],
                                      'nilai_spp_pajak_potongan' => $dat['nilai_spp_pajak_potongan'],
                                  ]);
                              }
                          }
                      }
                  }

                  $detail  = $data['detail'];
                  if(sizeOf($detail)){
                      foreach($detail as $dat){
                          $cek  = DB::table('ta_spm_rekening')->where('id_spm',$id)->where('kode_rekening',$dat['kode_rekening'])->first();
                          if(!$cek){
                              DB::table('ta_spm_rekening')->insert([
                                  'id_spm'  => $id,
                                  'kode_rekening' => $dat['kode_rekening'],
                                  'uraian' => $dat['uraian'],
                                  'jumlah' => $dat['jumlah'],
                              ]);
                          }
                      }
                  }

              }else{
                  // jika sudah ada di update
                  $cek2   = SPM::where('id_spm',$id)->where('asis_final_bend',0)->first();
                  if($cek2){
                      $pajak  = $data['pajak_potongan'];
                      if($pajak){
                          DB::table('ta_spm_pajak_potongan')->where('id_spm',$id)->update(['status'=>0]);
                          if(sizeOf($pajak)){
                              foreach($pajak as $dat){
                                  $cek  = DB::table('ta_spm_pajak_potongan')->where('id_spm',$id)->where('id_pajak_potongan',$dat['id_pajak_potongan'])->first();
                                  if(!$cek){
                                      DB::table('ta_spm_pajak_potongan')->insert([
                                          'id_spm'  => $id,
                                          'id_pajak_potongan' => $dat['id_pajak_potongan'],
                                          'nama_pajak_potongan' => $dat['nama_pajak_potongan'],
                                          'id_billing' => $dat['id_billing'],
                                          'nilai_spp_pajak_potongan' => $dat['nilai_spp_pajak_potongan'],
                                      ]);
                                  }else{
                                      DB::table('ta_spm_pajak_potongan')->where('id',$cek->id)->update([
                                          'id_pajak_potongan' => $dat['id_pajak_potongan'],
                                          'nama_pajak_potongan' => $dat['nama_pajak_potongan'],
                                          'id_billing' => $dat['id_billing'],
                                          'nilai_spp_pajak_potongan' => $dat['nilai_spp_pajak_potongan'],
                                          'status'  => 1,
                                      ]);
                                  }
                              }
                          }
                      }
                  }
              }

          }//batasan jenis LS
          else{
              $cek  = DB::table('ta_spm_cetak')->where('id_spm',$id)->first();
              if(!$cek){
                  DB::table('ta_spm_cetak')->insert([
                      'id_spm'  => $id,
                      'nama_daerah' => $data['nama_daerah'],
                      'tahun' => $data['tahun'],
                      'nomor_spm' => $data['nomor_spm'],
                      'tanggal_spm' => Carbon::parse($data['tanggal_spm'])->toDateTimeString(),
                      'nama_skpd' => $data['nama_skpd'],
                      'nama_sub_skpd' => $data['nama_sub_skpd'],
                      'keterangan_spm' => $data['keterangan_spm'],
                      'nilai_spm' => $data['nilai_spm'],
                      'nomor_spp' => $data['nomor_spp'],
                      'tanggal_spp' => Carbon::parse($data['tanggal_spp'])->toDateTimeString(),
                      'nama_ibukota' => $data['nama_ibu_kota'],
                      'nama_pa_kpa' => $data['nama_pa_kpa'],
                      'nip_pa_kpa' => $data['nip_pa_kpa'],
                      'jabatan_pa_kpa' => $data['jabatan_pa_kpa'],
                  ]);

              }
          }


        }


        return response()->json([
            'success' => false,
            'status'  => 'success',
            'message' => $message.' SPM Cetak',
            'pajak' => $pajak,
            'potongan' => $potongan,
            'data'  => $data,
            'payload' => $req->all(),
        ], 200);
    }

    static function InsertAgenSing($act,$act_id,$id_skpd,$jenis,$search){
          // ALTER TABLE `ta_singkron` ADD `search` VARCHAR(255) NULL AFTER `act_id`;
          $cek  = DB::table('ta_singkron')->where('act',$act)->where('search',$search)->first();
          if($cek){
              DB::table('ta_singkron')->where('id',$cek->id)->update([
                  'status'  => 0,
              ]);
          }else{
              DB::table('ta_singkron')->insert([
                  'act' => $act,
                  'act_id'  => $act_id,
                  'id_skpd' => $id_skpd,
                  'jenis' => $jenis,
                  'search'  => $search,
                  'status'  => 0,
              ]);
          }
    }

}
