<?php

namespace App\Http\Controllers\SPM;

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


use App\Models\User;
use App\Models\SPM;
use App\Models\SPM_POTONGAN;
use App\Models\SPM_VERIFIED;

use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;
use App\Http\Controllers\ApiAsis;

class SPMController extends Controller
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

        $ha = HALocal::HakAksesUser($nip,2);
        if(!$ha['lihat']){
           return response()->json([
               'success' => false,
               'message' => $message.' '.$nip,
               'ha'  => $ha
           ],200);
        }
        $super = 0;
        $user  = User::where('nip_user',$nip)->where('status',1)->first();
        $success = true; $message = 'Sukses Get Data SPM';
        $userl  = HALocal::GetTableUser($nip);
        $query  = SPM::orderby('tanggal_spm','desc');
        if($req->search){
            $query->where('nomor_spm','LIKE','%'.$req->search.'%');
            self::InsertAgenSing($req);
        }
        $data  = $query->where('id_skpd',$user->id_opd)->orderby('id_spm','desc')->paginate(10);
        $data->getCollection()->transform(function ($value) {
            $value->info_verified = $value->verified();
            return $value;
        });

        if(sizeOf($data)) $success = true;
        else{
            if(!$req->search) $success = true;
            else{
                if($req->repeat > 2) $success = true;
                else $success = false;
            }
        }

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

    static function Find($req,$id_spm){
         $success = false; $message = 'Otoritas Tidak Diizinkan';
         $super   = 1;
         $token = $req->bearerToken();
         $user  = JWT::CheckJWT($token);
         if(!$user['success']){
            return response()->json(['success' => false,'message' => $message], 401);
         }
         $nip  = $user['data']['nip'];

         if(!$req->veri){
            $ha = HALocal::HakAksesUser($nip,2);
         }else{
            $ha = HALocal::HakAksesUser($nip,3);
         }

         if(!$ha['lihat']){
            return response()->json([
                'success' => false,
                'message' => $message.' '.$nip,
                'ha'  => $ha
            ],200);
         }
         $super = 0;
         $user  = User::where('nip_user',$nip)->where('status',1)->first();
         $success = true; $message = 'Sukses Get Data SPM';
         $userl  = HALocal::GetTableUser($nip);
         //
         $query  = SPM::where('id_spm',$id_spm)->with('spm_cetak')->with('pajak_potongan')
                  ->with('rekening')->with('dokumen');
         if(!$req->veri) $query->where('id_skpd',$user->id_opd);
         $data  = $query->first();
         if($data->spm_cetak){
              $data->spm_cetak->spp();
              $data->spm_cetak->spp_cetak();
              $is_rek  = is_numeric($data->rekanan_nomor_rekening);
         }
         $data->verified();

         if(isset($data->rekanan_nomor_rekening)) $is_rek  = is_numeric($data->rekanan_nomor_rekening);
         else $is_rek = false;

         $data->Cek_Ceklist();
         $data->transaksi();

         $data->pajak_potongan->transform(function ($value) {
             // $value->is_pajak();
             if($value->id_pajak_potongan < 12){
                 $btn  = false;
                 $status_message = $value->status_message;
                 $valid  = $value->is_valid;
                 if($valid == 1){
                     $btn_disable = true;
                     $btn_status  = "bg-success";
                 }else{
                     $btn_disable = false;
                     $btn_status  = "bg-danger";
                 }
             }else{
                 $btn  = true;
                 $btn_status  = "bg-success";
                 $status_message = "VALID";
                 $valid = 1;
             }
             $value->btn_disable = $btn;
             $value->status_message = $status_message;
             $value->is_valid = $valid;
             $value->btn_status = $btn_status;
             return $value;
         });
         $data->terlampir = !$is_rek;

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

    static function FinalBendahara($req){
        $spm  = DB::table('ta_spm')->where('id_spm',$req->id_spm)->first();
        if($spm){
            DB::table('ta_spm')->where('id_spm',$req->id_spm)->update([
                'asis_final_bend' => 1,
                'asis_final_veri' => 0,
            ]);
            $success = true;
            $message = 'Sukses Final SPM';
            $asis_final_bend = 1;

            try {
                // INI UNTUK NOTIF KE PETUGAS VERIFIKASI
                $kirim_pesan = '*Informasi Pengajuan*';
                $kirim_pesan .= ' \n\n'.'*'.$spm->keterangan_spm.'*';
                $kirim_pesan .= ' \n\n,Silahkan diverifikasi Terimakasih';
                $tim_sp2d  = DB::table('ta_otoritas')->where('id_ref',3)->where('lihat',1)->get();
                foreach($tim_sp2d as $dat){
                    $user_api = ApiAsis::GetHtHUsers($dat->nip);
                    if($user_api->success){
                        $hp = $user_api->data->hp;
                        ApiAsis::SendMessage($hp,$kirim_pesan);
                    }
                }
            }catch (\Exception $e) {
                $message = 'Sukses Final SPM !!!';
                // $message = $e->getMessage();
            }

        }else{
            $success = false;
            $message = 'Gagal Final SPM';
            $asis_final_bend = 0;
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $spm,
            'asis_final_bend' => $asis_final_bend,
        ], 200);

    }

    static function InsertAgenSing($req){
          // ALTER TABLE `ta_singkron` ADD `search` VARCHAR(255) NULL AFTER `act_id`;
          $cek  = DB::table('ta_singkron')->where('act','spm_search')->where('search',$req->search)->first();
          if($cek){
              DB::table('ta_singkron')->where('id',$cek->id)->update([
                  'status'  => 0,
              ]);
          }else{
              DB::table('ta_singkron')->insert([
                  'act' => 'spm_search',
                  'act_id'  => 0,
                  'id_skpd' => 0,
                  'jenis' => '-',
                  'search'  => $req->search,
                  'status'  => 0,
              ]);
          }
    }

    static function EditPotongan($req,$id){
        $success = true; $message = 'Sukses Edit Potongan';
        SPM_POTONGAN::where('id',$req->id)->where('id_spm',$req->id_spm)->update([
            'id_billing'  => $req->id_billing,
            'nilai_spp_pajak_potongan' => $req->nilai_spp_pajak_potongan,
            'mata_anggaran' => $req->mata_anggaran,
            'jenis_setoran' => $req->jenis_setoran,
            'masa_pajak' => $req->masa_pajak,
            'tahun_pajak' => $req->tahun_pajak,
            'is_valid'  => 0,
            'status_message'  => 'Belum Divalidasi',
        ]);
        $data  = SPM_POTONGAN::where('id',$req->id)->where('id_spm',$id)->first();
        $data->status_message = 'Berhasil Diupdate, Silahkan Validasi';
        if($data->id_pajak_potongan < 12){
            $btn  = false;
            $status_message = $data->status_message;
            $valid  = $data->is_valid;
            if($valid == 1){
                $btn_disable = true;
                $btn_status  = "bg-success";
            }else{
                $btn_disable = false;
                $btn_status  = "bg-danger";
            }
        }else{
            $btn  = true;
            $btn_status  = "bg-success";
            $status_message = "VALID";
            $valid = 1;
        }
        $data->btn_disable = $btn;
        $data->status_message = $status_message;
        $data->is_valid = $valid;
        $data->btn_status = $btn_status;

        return response()->json([
           'success' => $success,
           'message' => $message,
           'data' => $data,
           'payload'  => $req->all(),
        ], 200);
    }

    static function UploadDokumen($id_spm,$req){
          $token = $req->bearerToken();
          $user  = JWT::CheckJWT($token);
          if(!$user['success']){
             return response()->json(['success' => false,'message' => $message], 401);
          }
          $nip  = $user['data']['nip'];

          $messages = [
            'tgl_awal.required' => 'Silahkan Isi Tanggal',
            'slug_pelanggan_id.required' => 'Silahkan Isi Penerima',
            'no_resi.required'  => 'Silahkan Isi No. Resi',
            'no_resi.unique'  => 'No Resi Sudah Terdaftar',
            'berat.required'  => 'Silahkan Isi Berat Barang',
            'keterangan.required'  => 'Silahkan Isi Keterangan',
            'slug_gudang.required'  => 'Otoritas Gudang Belum Disetting',
            'jitem.required'  => 'Input Jumlah Item Barang',
            'file.max'  => 'Ukuran file tidak boleh lebih dari :max kilobyte.',
          ];

          $validator = Validator::make($req->all(), [
              'uraian'   => 'required',
              'id_ref'   => 'required',
              'file'   => 'required|file|max:40240',
          ], $messages);


          if ($validator->fails()) {
              $data  = $validator->errors()->all();
              $error = $data[0];
              $errors = $validator->errors();
              return response()->json(['success'=>false, 'message' => $error, 'error'=>$errors ], 200);
          }

          try {
              if ($req->hasFile('file')) {
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
              'id_spm'  => $id_spm,
              'id_ref'  => $req->id_ref,
              'uraian'  => $req->uraian,
              'file'  => $file,
              'created_by'  => $nip,
              'updated_by'  => $nip,
          ]);

          $message = 'Tambah Data Berhasil';
          $success = true;
          $data = DB::table('ta_spm_dokumen')->where('id_spm',$id_spm)->get();
          return response()->json([
              'success' => $success,
              'message' => $message,
              'data'  => $data,
          ], 200);
    }

    static function AddTransaksi($req,$id_spm){
        $messages = [
          'account_bank.required' => 'Silahkan Kode Bank',
          'asn_type.required' => 'Silahkan Isi Tipe Rekanan',
          'rekanan_nama_rekening.required'  => 'Silahkan Nama Rekning Bank',
          'rekanan_nomor_rekening.required'  => 'Silahkan Isi Nomor Rekening Bank',
        ];

        $validator = Validator::make($req->all(), [
            'asn_type'   => 'required',
            'account_bank'   => 'required',
            'rekanan_nama_rekening'   => 'required',
            'rekanan_nomor_rekening'   => 'required',
            // 'account_bank'   => 'required',
            // 'account_bank'   => 'required',
        ], $messages);


        if ($validator->fails()) {
            $data = $validator->errors();
            return response()->json($data, 422);
        }

        if($req->id){
            DB::table('ta_transaksi')->where('id',$req->id)->update([
                'rekanan_nama_rekening'  => $req->rekanan_nama_rekening,
                'rekanan_nomor_rekening'  => $req->rekanan_nomor_rekening,
                'rekanan_nik'  => $req->rekanan_nik,
                'status_code' => 0,
            ]);

            $message = 'Sukses Edit Data';
            $success = true;
            $data = DB::table('ta_transaksi')->where('id_spp',$id_spp)->get();
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data'  => $data,
            ], 200);
        }

        $nomor  = DB::table('ta_transaksi')->where('id_spm',$id_spm)->count();
        $tx_partner_id  = bin2hex(random_bytes(12));
        $spm  = SPM::where('id_spm',$id_spm)->first();
        $tx_type  = 'SP2D|'.$spm->jenis_spp.'|'.strtoupper($spm->jenis_spm);
        DB::table('ta_transaksi')->insert([
            'record_no' => $nomor++,
            'id_spm'  => $id_spm,
            'tx_partner_id'  => $tx_partner_id,
            'account_bank'  => $req->account_bank,
            'rekanan_nama_rekening'  => $req->rekanan_nama_rekening,
            'rekanan_nomor_rekening'  => $req->rekanan_nomor_rekening,
            'rekanan_nik'  => $req->rekanan_nik,
            'kode_wilayah'  => '21.05',
            'total_data'  => 1,
            'tx_type' => $tx_type,
            'status_code' => 0,
            'keterangan_pembayaran' => $spm->keterangan_spm,
        ]);

        $message = 'Sukses Tambah Data';
        $success = true;
        $data = DB::table('ta_transaksi')->where('id_spm',$id_spm)->get();
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
        ], 200);


    }

    static function ProsesVerifikasi($req,$id_spm){
        $id_spm  = $req->id_spm;
        $token = $req->bearerToken();
        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 401);
        }
        $nip  = $user['data']['nip'];
        $name  = $user['data']['name'];


        $message = 'Gagal Proses Verifikasi';
        $success = false;
        $cek     = SPM::where('id_spm',$id_spm)->first();
        if($cek){
            SPM::where('id_spm',$id_spm)->update(['asis_proses_veri'=>1]);
            SPM_VERIFIED::insert([
                'id_spm'  => $id_spm,
                'name'  => $name,
                'nip' => $nip,
            ]);
            $success = true;
            $message = 'Proses Verifikasi Sukses';
        }
        return response()->json([
            'success' => $success,
            'message' => $message,
            'id_spm'  => $id_spm,
        ], 200);
    }

    static function GetUserAsis($req){
        $token = $req->bearerToken();

        return JWT::GetUser(3,$token);
    }

    static function SearchUserAsis($req){
        $token = $req->bearerToken();
        $data =  JWT::SearchUser($req,$token);

        return response()->json([
            'success' => false,
            'message' => "Gagal",
            'data'  => $data,
            'payload' => $req->all()
        ], 200);
    }

}
