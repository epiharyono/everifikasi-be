<?php

namespace App\Http\Controllers\SPPD;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Crypt;
use Input;
use View;
use Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use GuzzleHttp\Client as GuzzleHttpClient;
use Carbon\Carbon;


use App\Models\User;
use App\Models\SPPD;
use App\Models\SPPD_POTONGAN;
use App\Models\SPPD_CETAK;
use App\Models\Ta_Rekanan;

use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;
use App\Http\Controllers\Sipd\SipdController as SIPD;
use App\Http\Controllers\BANK\AccessToken;
use App\Http\Controllers\BANK\ApiRequest;
use App\Http\Controllers\SPPD\KasdaController as KASDA;

class VeriController extends Controller
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

        $ha = HALocal::HakAksesUser($nip,4);
        if(!$ha['lihat']){
           return response()->json([
               'success' => false,
               'message' => $message.' '.$nip,
               'ha'  => $ha
           ],200);
        }
        $super = 0;
        $success = true; $message = 'Sukses Get Data SPPD';
        $userl  = HALocal::GetTableUser($nip);
        $query  = SPPD::orderby('tanggal_spm','desc');
        if($req->search){
            $query->where('nomor_sp_2_d','LIKE','%'.$req->search.'%');
            self::InsertAgenSing($req);
        }
        if($req->id_skpd){
            $query->where('id_skpd',$req->id_skpd);
        }
        if($req->status_veri){
            $query->where('asis_final',$req->status_veri);
        }
        $data  = $query->orderby('id_sp_2_d','desc')->paginate(10);
        $data->getCollection()->transform(function ($value) {
            $value->getStatus();
            return $value;
        });

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
            'ha'  => $ha,
            'userl' => $userl,
            'user'  => $user,
            'payload' => $req->all()
        ], 200);

    }

    static function InsertAgenSing($req){
          // ALTER TABLE `ta_singkron` ADD `search` VARCHAR(255) NULL AFTER `act_id`;
          DB::table('ta_singkron')->insert([
              'act' => 'sp2d_search',
              'act_id'  => 0,
              'id_skpd' => 0,
              'jenis' => '-',
              'search'  => $req->search,
              'status'  => 0,
          ]);
    }

    static function Final($req){
         $success = true; $message = 'Sukses Update Data';
         $date = Carbon::now()->setTimezone('Asia/Jakarta');
         $date = $date->addHour(2);
         $date = $date->toIso8601String();

         $random  =  bin2hex(random_bytes(12));
         $token = $req->bearerToken();
         $user  = JWT::CheckJWT($token);
         if(!$user['success']){
            return response()->json(['success' => false,'message' => $message], 401);
         }
         $nip  = $user['data']['nip'];

         if($req->status == 2){
            SPPD::where('id_sp_2_d',$req->id_sp_2_d)->update(['asis_final' => 2]);
            return response()->json([
              'success' => true,
              'message' => $message,
              'asis_final_veri' => 2,
            ], 200);
         }

         // Kasda Online
         return KASDA::FinalSPPD($req);

         $payload = $req->all();

         // $sp2d_cetak  = SPPD_CETAK::where('id_sp_2_d',$req->id_sp_2_d)->first();

         $sp2d  = SPPD::where('id_sp_2_d',$req->id_sp_2_d)->with('spm')->with('cetak')->with('potongan')->with('rekening')->with('bank')->first();
         if(!$sp2d->cetak->tx_overbook_id){
              $external_id  = bin2hex(random_bytes(12));
              DB::table('ta_sppd_cetak')->where('id_sp_2_d',$req->id_sp_2_d)->update([
                'tx_overbook_id' => $random,
                'external_id' => $external_id,
              ]);
         }else{
              $random = $sp2d->cetak->tx_overbook_id;
              $external_id  =$sp2d->cetak->external_id;
         }

         if($req->status == 1){
            // ini hanya untuk dev
              $external_id  = bin2hex(random_bytes(12));
              $random  = bin2hex(random_bytes(12));
              DB::table('ta_sppd_cetak')->where('id_sp_2_d',$req->id_sp_2_d)->update([
                 'tx_overbook_id' => $random,
                 'external_id' => $external_id,
              ]);

              $transaksi  = DB::table('ta_transaksi')->where('id_spm',$sp2d->spm->id_spm)->get();
              foreach($transaksi as $dat_trans){
                    $tx_partner_id_new  = bin2hex(random_bytes(12));
                    DB::table('ta_transaksi')->where('id',$dat_trans->id)->update([
                        'tx_partner_id' => $tx_partner_id_new
                    ]);
              }
         }

         $account_number = $sp2d->cetak->nomor_rekening;
         if($sp2d->jenis_ls_sp_2_d) $type = strtoupper($sp2d->jenis_ls_sp_2_d);
         else $type = 'BENDAHARA';

         $type  = 'SP2D|'.$sp2d->jenis_sp_2_d.'|'.$type;

         $pajaks  = DB::table('ta_sppd_potongan')->where('id_sp_2_d',$req->id_sp_2_d)->where('id_pajak_potongan','<',12)->get();
         foreach($pajaks as $dat){
             // PPh22 => pihak ke tiga nama_npwp 9
             // PPh23, PPN dan PPh21 => nama dinas
              $nama_wajib_pajak = '';
              $alamat_wajib_pajak = '';
              $npwp = '';
              if($dat->id_pajak_potongan == 9){
                  $rekanan = Ta_Rekanan::where('nomor_rekening',$sp2d->cetak->no_rek_pihak_ketiga)->first();
                  if($rekanan){
                      $nama_wajib_pajak = $rekanan->nama_perusahaan;
                      $alamat_wajib_pajak = $rekanan->alamat_perusahaan;
                      $npwp = $rekanan->npwp;
                  }
              }else{
                  // rekening terlampir
                  $rekanan = Ta_Rekanan::where('id_skpd',$sp2d->id_skpd)->where('nomor_rekening',$sp2d->cetak->no_rek_pihak_ketiga)->first();
                  if($rekanan){
                      $nama_wajib_pajak = $rekanan->nama_perusahaan;
                      $alamat_wajib_pajak = $rekanan->alamat_perusahaan;
                      $npwp = $rekanan->npwp;
                  }
              }

              if(strlen($npwp) < 3 || strlen($nama_wajib_pajak) < 2 || strlen($alamat_wajib_pajak) < 2){
                    return response()->json([
                        'success' => false,
                        'message' => 'Data NPWP, Nama dan Alamat Tidak Boleh Kosong',
                    ], 200);
              }
             // $rekanan = Ta_Rekanan::where('nomor_rekening',$dat_trans->rekanan_nomor_rekening)->first();
             $pajak[] = [
               "include_pajak"  => true,
               "nominal_pajak"  => "".$dat->nilai_sp2d_pajak_potongan.".00",
               "npwp"  => $npwp,
               "nama_wajib_pajak"  => $nama_wajib_pajak,
               "alamat_wajib_pajak"  => $alamat_wajib_pajak,
               "nomor_object_pajak"  => "",
               "mata_anggaran"  => "411121",
               "jenis_setoran"  => "100",
               "masa_pajak"  => "11-11",
               "tahun_pajak"  => "2024",
               "no_spm"  => $sp2d->nomor_spm,
               "id_billing"  => $dat->id_billing
             ];
         }

         if(!sizeOf($pajaks)){
            $pajak = [];
         }

         $potongan  = SPPD_POTONGAN::select('id_pajak_potongan as id','nama_pajak_potongan as nama_potongan','nilai_sp2d_pajak_potongan as nominal')
                      ->where('id_sp_2_d',$req->id_sp_2_d)->where('id_pajak_potongan','>',11)->get();
         $potongan->transform(function ($value) {
              // $potongan->nominal =
              $value->nilai_sp2d_pajak_potongan();
              $value->nama_potongan();

              return $value;
         });

         $transaksi  = DB::table('ta_transaksi')->where('id_spm',$sp2d->spm->id_spm)->get();
         foreach($transaksi as $dat_trans){
              $rekanan = Ta_Rekanan::where('nomor_rekening',$dat_trans->rekanan_nomor_rekening)->first();
              if(!$rekanan){
                  return response()->json([
                      'success' => false,
                      'message' => 'Data Rekanan Tidak Ditemukan',
                  ], 200);
              }
              $data[]  = [
                    "tx_partner_id"  => $dat_trans->tx_partner_id,
                    "note"  => $dat_trans->keterangan_pembayaran,
                    "amount"  => "".$dat_trans->jumlah_ditransfer.".00",
                    "record_no"  => $dat_trans->record_no,
                    "payment_method"  => "rekening",
                    "recipient_info"  => [
                      "account_bank"  => "119",
                      "account_number"  => $dat_trans->rekanan_nomor_rekening,
                      "account_bank_name"  => $dat_trans->rekanan_nama_rekening,
                      "additional_data"  => [
                        "npwp_wp"  => $rekanan->npwp,
                        "nik_wp"  => $rekanan->nik,
                        "nama_wp"  => $rekanan->nama_rekening,
                        "alamat_wp"  => $rekanan->alamat_perusahaan,
                        "nop_wp"  => "",
                        "identity_type"  => "1",
                        "identity_number"  => "",
                        "resident"  => "Y",
                        "asn_type"  => "REKANAN"
                      ]
                    ]
                ];
         }

         // 4. Over booking  = Overbooking adalah perpindahan dana dari Rekening Kas Umum Daerah (RKUD) ke rekening penerima.
         $url = 'overbooking';
         $body = [
             "user_id"  => $nip,
             "tx_info"  => [
               "tx_overbook_id"  => $random,
               "no_sp2d"  => $sp2d->nomor_sp_2_d,
               "desc_sp2d"  => $sp2d->keterangan_sp_2_d,
               "nominal_sp2d"  => "".$sp2d->nilai_sp_2_d.".00",
               "kode_wilayah"  => "21.05",
               "is_realtime"  => false,
               "execution_time"  => $date,
               "total_data"  => sizeOf($data),
               "tx_type"  => $type,
               "sender_info"  => [
                 "account_number"  => $account_number,
                 "account_bank"  => "119",
                 "additional_info"  => [
                   "pajak"  => $pajak,
                   "potongan"  => $potongan,
                 ]
               ]
             ],
             "data" => $data
         ];

         DB::table('ta_ob_history')->insert([
            'id_sp2d' => $sp2d->id_sp_2_d,
            'external_id' => $external_id,
            'body'  => json_encode($body),
            'created_by'  => $nip,
         ]);
         return response()->json([
           'success' => false,
           'message' => 'Hanya untuk testing',
           'asis_final_veri' => 2,
           'url'  => $url,
           'body' => $body,
           'external_id'  => $external_id,
         ], 200);

         $proses  = ApiRequest::API($url,$body,$external_id);
         if($proses->status->code == "100"){

         }
         DB::table('ta_transaksi')->where('id_spm',$sp2d->spm->id_spm)->update([
            'status_code' => $proses->status->code,
            'status_message'  => $proses->status->message,
         ]);

         return response()->json([
             'success' => $success,
             'message' => $message,
             'proses_bank'  => DB::table('ta_sppd_bank')->where('id_sp2d',$sp2d->id_sp_2_d)->first(),
             'sp2d' => $sp2d,
             'sp2d_cetak' => $sp2d,
             'payload'  => $body,
             'final'  => $req->status,
         ], 200);
    }


    static function UploadDokumen($id,$req){
          $token = $req->bearerToken();
          $user  = JWT::CheckJWT($token);
          if(!$user['success']){
             return response()->json(['success' => false,'message' => $message], 401);
          }
          $nip  = $user['data']['nip'];

          // ini sementara ya
          $sp2d  = SPPD::where('id_sp_2_d',$id)->first();

          $messages = [
            'file' => 'Silahkan Isi Uraian',
          ];

          $validator = Validator::make($req->all(), [
              'file'   => 'required',
              'id_spm'  => 'required'
          ], $messages);


          if ($validator->fails()) {
              $data = $validator->errors();
              return response()->json($data, 422);
          }

          try {
              if ($req->hasFile('file')) {
                // Storage::delete("images/profiles/" . $user->image);
                $folder = date('Y/m/d');
                $file_name = time() . '.' . $req->file('file')->getClientOriginalExtension();
                $file = $req->file("file");
                $file->storeAs($folder, str_replace(' ', '_', $file_name),'my_files');
                $file = $folder.'/'.$file_name;
              }
          } catch (\Exception $e) {
              return response()->json(['success' => false, 'message' => $e->getMessage()]);
          }

          $slug  =  bin2hex(random_bytes(5));
          DB::table('ta_spm_dokumen')->insert([
              'slug'  => $slug,
              'id_spm'  => $sp2d->id_spm,
              'id_ref'  => 4,
              'uraian'  => 'Dokumen SP2D',
              'veri_notes'  => '-',
              'file'  => $file,
              'created_by'  => $nip,
              'updated_by'  => $nip,
          ]);

          $message = 'Tambah Data Berhasil';
          $success = true;
          $data = DB::table('ta_spm_dokumen')->where('id_spm',$sp2d->id_spm)->get();
          return response()->json([
              'success' => $success,
              'message' => $message,
              'data'  => $data,
          ], 200);
    }


}
