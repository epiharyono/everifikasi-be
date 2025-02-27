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

use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;

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

        $ha = HALocal::HakAksesUser($nip,3);
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
            // self::InsertAgenSing($req);
        }

        if($req->id_skpd){
            $query->where('id_skpd',$req->id_skpd);
        }

        if($req->status_veri == 1){
            $query->where('asis_final_veri',1);
            $query->where('asis_final_bend',1);
        }
        if($req->status_veri == 2){
            $query->where('asis_final_veri',0);
            $query->where('asis_final_bend',1);
        }
        if($req->status_veri == 3){
            $query->where('asis_final_veri',2);
        }
        $query->where('asis_final_bend',1);

        $data  = $query->orderby('id_spm','desc')->paginate(50);
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

    static function UpdateCekList($req){
        $message = 'Data Berhasil Diupdate';
        $success = true;
        $token = $req->bearerToken();

        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 401);
        }
        $nip  = $user['data']['nip'];

        if($req->valid) $valid  = 1;
        else $valid = 0;
        DB::table('ta_ceklist')->where('id',$req->id)->update([
            'valid' => $valid,
            'updated_by'  => $nip,
        ]);
        return response()->json([
            'success' => $success,
            'message' => $message,
        ], 200);
    }

    static function EditCekList($req,$id_spm){
        $message = 'Data Berhasil Diupdate';
        $success = true;
        $token = $req->bearerToken();

        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 401);
        }
        $nip  = $user['data']['nip'];

        DB::table('ta_ceklist')->where('id_spm',$id_spm)->where('id',$req->id)->update([
            'catatan' => $req->catatan,
            'updated_by'  => $nip,
        ]);
        return response()->json([
            'success' => $success,
            'message' => $message,
        ], 200);

    }

    static function SaveNotesDokumen($req){
        $message = 'Data Berhasil Diupdate';
        $success = true;
        $token = $req->bearerToken();

        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 401);
        }
        $nip  = $user['data']['nip'];

        $dokumen  = DB::table('ta_spm_dokumen')->where('id',$req->id)->where('slug',$req->slug)->first();
        if($dokumen){
              DB::table('ta_spm_dokumen')->where('id',$dokumen->id)->update([
                  'veri_notes' => $req->veri_notes,
                  'updated_by'  => $nip,
              ]);
        }
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $dokumen,
        ], 200);
    }

    static function FinalVerifikasi($req){
        $message = 'Sukses Update Data';
        $success = true;
        $data = '';
        if($req->status == 1){
            $data = DB::table('ta_spm')->where('id_spm',$req->id_spm)->update([
                'asis_final_veri' => $req->status,
            ]);

        }elseif($req->status == 2){
            $data = DB::table('ta_spm')->where('id_spm',$req->id_spm)->update([
                'asis_final_veri' => 2,
                'asis_final_bend' => 0,
            ]);
        }
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
            'asis_final_veri' => $req->status,
        ], 200);
    }

    static function XXXFind($req,$id_spm){
         $success = false; $message = 'Otoritas Tidak Diizinkan';
         $super   = 1;
         $token = $req->bearerToken();
         $user  = JWT::CheckJWT($token);
         if(!$user['success']){
            return response()->json(['success' => false,'message' => $message], 401);
         }
         $nip  = $user['data']['nip'];

         $ha = HALocal::HakAksesUser($nip,3);
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
         $data  = SPM::where('id_skpd',$user->id_opd)->where('id_spm',$id_spm)->with('spm_cetak')->with('pajak_potongan')
                  ->with('rekening')->with('dokumen')->first();
         if($data->spm_cetak){
              $data->spm_cetak->spp();
              $data->spm_cetak->spp_cetak();
         }

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

    static function XXXFinalBendahara($req){
        $spm  = DB::table('ta_spm')->where('id_spm',$req->id_spm)->first();
        if($spm){
            DB::table('ta_spm')->where('id_spm',$req->id_spm)->update([
                'asis_final_bend' => 1,
                'asis_final_veri' => 0,
            ]);
            $success = true;
            $message = 'Sukses Final SPM';
            $asis_final_bend = 1;
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

    static function XXXInsertAgenSing($req){
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

    static function XXXEditPotongan($req,$id){
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

    static function XXXUploadDokumen($id_spm,$req){
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
