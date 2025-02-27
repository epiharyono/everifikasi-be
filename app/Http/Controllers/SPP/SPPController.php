<?php

namespace App\Http\Controllers\SPP;

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
use App\Models\SPP;

use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;
use App\Http\Controllers\Sipd\SipdController as SIPD;

class SPPController extends Controller
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
        $success = true; $message = 'Sukses Get Data Users';
        $userl  = HALocal::GetTableUser($nip);
        $query  = SPP::orderby('tanggal_spp','desc');
        if($req->search){
            $query->where('nomor_spp','LIKE','%'.$req->search.'%');
            self::InsertAgenSing($req);
        }

        $data  = $query->where('id_skpd',$user->id_opd)->orderby('id_spp','desc')->paginate(10);
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
              'act' => 'spp_search',
              'act_id'  => 0,
              'id_skpd' => 0,
              'jenis' => '-',
              'search'  => $req->search,
              'status'  => 0,
          ]);
    }

    static function FindByID($id,$req){
        $success = false; $message = 'Otoritas Tidak Diizinkan';
        $super   = 1; $sync_gaji   = false;
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
        $success = true; $message = 'Sukses Get Data SPP';
        $user  = User::where('nip_user',$nip)->where('status',1)->first();

        $data  = SPP::where('id_spp',$id)->where('id_skpd',$user->id_opd)->with('skpd')->with('cetak')->with('rekening')->first();
        if($data){
            // ceklist
            $data->Cek_Ceklist();
            $is_rek  = is_numeric($data->rekanan_nomor_rekening);
            if($data->jenis_spp == 'LS' && $data->bulan_gaji == 0 && $is_rek == true){
                $cek  = DB::table('ta_transaksi')->where('id_spp',$data->id_spp)->first();
                if(!$cek){
                    $tx_partner_id = bin2hex(random_bytes(12));
                    $tx_type  = 'SP2D|'.$data->jenis_spp.'|'.strtoupper($data->jenis_ls_spp);
                    DB::table('ta_transaksi')->insert([
                        'id_spp'  => $data->id_spp,
                        'record_no' => 1,
                        'account_bank'  => 119,
                        'rekanan_nama_rekening' => $data->rekanan_nama_rekening,
                        'rekanan_nomor_rekening' => $data->rekanan_nomor_rekening,
                        'rekanan_nik' => $data->rekanan_nik,
                        'keterangan_pembayaran' => $data->keterangan_spp,
                        'asn_type' => 'rekanan',
                        'kode_wilayah'  => '21.05',
                        'tx_partner_id'  => $tx_partner_id,
                        'total_data'  => 1,
                        'tx_type' => $tx_type,
                        'status_code' => 0
                    ]);
                }
            }
            elseif($data->is_gaji){
                $cek  = DB::table('ta_transaksi')->where('id_spp',$data->id_spp)->count();
                if(!$cek){
                    $record = 0;
                    $gaji = DB::table('ta_gaji')->where('id_skpd',$data->id_skpd)->where('bulan_gaji',$data->bulan_gaji)->get();
                    if(!sizeOf($gaji)){
                        $sync_gaji = true;
                    }

                    $pot = DB::table('ta_gaji')->where('jenis_gaji',1)->where('bulan_gaji',$data->bulan_gaji)->where('id_skpd',$data->id_skpd)->sum('jumlah_potongan');
                    $tf  = DB::table('ta_gaji')->where('jenis_gaji',1)->where('bulan_gaji',$data->bulan_gaji)->where('id_skpd',$data->id_skpd)->sum('jumlah_ditransfer');
                    $total = $pot + $tf;
                    if($total == $data->nilai_spp){
                          $gaji = DB::table('ta_gaji')->where('jenis_gaji',1)->where('id_skpd',$data->id_skpd)->where('bulan_gaji',$data->bulan_gaji)->get();
                          foreach($gaji as $dat){
                              $tx_partner_id = bin2hex(random_bytes(12));
                              $tx_type  = 'SP2D|'.$data->jenis_spp.'|'.strtoupper($data->jenis_ls_spp);
                              $cek  = DB::table('ta_transaksi')->where('id_gaji_pegawai',$dat->id_gaji_pegawai)->first();
                              if(!$cek){
                                  DB::table('ta_transaksi')->insert([
                                      'id_spp'  => $data->id_spp,
                                      'record_no' => $record++,
                                      'id_gaji_pegawai'  => $dat->id_gaji_pegawai,
                                      'account_bank'  => $dat->kode_bank,
                                      'rekanan_nama_rekening' => $dat->nama_pegawai,
                                      'rekanan_nomor_rekening' => $dat->nomor_rekening_bank_pegawai,
                                      'rekanan_nik' => $dat->nik_pegawai,
                                      'keterangan_pembayaran' => $data->keterangan_spp,
                                      'npwp' => $dat->npwp_pegawai,
                                      'alamat' => $dat->alamat,
                                      'jumlah_ditransfer' => $dat->jumlah_ditransfer,
                                      'asn_type' => 'ASN',
                                      'kode_wilayah'  => '21.05',
                                      'tx_partner_id'  => $tx_partner_id,
                                      'total_data'  => 1,
                                      'tx_type' => $tx_type,
                                      'status_code' => 0
                                  ]);
                              }
                          }

                    }else{
                        $pot = DB::table('ta_gaji')->where('jenis_gaji',2)->where('bulan_gaji',$data->bulan_gaji)->where('id_skpd',$data->id_skpd)->sum('jumlah_potongan');
                        $tf  = DB::table('ta_gaji')->where('jenis_gaji',2)->where('bulan_gaji',$data->bulan_gaji)->where('id_skpd',$data->id_skpd)->sum('jumlah_ditransfer');
                        $total = $pot + $tf;
                        if($total == $data->nilai_spp){
                            $gaji = DB::table('ta_gaji')->where('jenis_gaji',2)->where('id_skpd',$data->id_skpd)->where('bulan_gaji',$data->bulan_gaji)->get();
                            foreach($gaji as $dat){
                                $tx_partner_id = bin2hex(random_bytes(12));
                                $tx_type  = 'SP2D|'.$data->jenis_spp.'|'.strtoupper($data->jenis_ls_spp);
                                $cek  = DB::table('ta_transaksi')->where('id_gaji_pegawai',$dat->id_gaji_pegawai)->first();
                                if(!$cek){
                                    DB::table('ta_transaksi')->insert([
                                        'id_spp'  => $data->id_spp,
                                        'record_no' => $record++,
                                        'id_gaji_pegawai'  => $dat->id_gaji_pegawai,
                                        'account_bank'  => $dat->kode_bank,
                                        'rekanan_nama_rekening' => $dat->nama_pegawai,
                                        'rekanan_nomor_rekening' => $dat->nomor_rekening_bank_pegawai,
                                        'rekanan_nik' => $dat->nik_pegawai,
                                        'keterangan_pembayaran' => $data->keterangan_spp,
                                        'npwp' => $dat->npwp_pegawai,
                                        'alamat' => $dat->alamat,
                                        'jumlah_ditransfer' => $dat->jumlah_ditransfer,
                                        'asn_type' => 'ASN',
                                        'kode_wilayah'  => '21.05',
                                        'tx_partner_id'  => $tx_partner_id,
                                        'total_data'  => 1,
                                        'tx_type' => $tx_type,
                                        'status_code' => 0
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
            elseif($data->is_tpp){
                $record = 0;
                $gaji = DB::table('ta_gaji')->where('id_skpd',$data->id_skpd)->where('bulan_gaji',$data->bulan_gaji)->get();
                if(!sizeOf($gaji)){
                    $sync_gaji = true;
                }
                $cek  = DB::table('ta_transaksi')->where('id_spp',$data->id_spp)->first();
                if(!$cek){
                    foreach($gaji as $dat){
                        $tx_partner_id = bin2hex(random_bytes(12));
                        $tx_type  = 'SP2D|'.$data->jenis_spp.'|'.strtoupper($data->jenis_ls_spp);
                        DB::table('ta_transaksi')->insert([
                            'id_spp'  => $data->id_spp,
                            'record_no' => $record++,
                            // 'account_bank'  => $dat->kode_bank,
                            'account_bank'  => '119',
                            'rekanan_nama_rekening' => $dat->nama_pegawai,
                            'rekanan_nomor_rekening' => $dat->nomor_rekening_bank_pegawai,
                            'rekanan_nik' => $dat->nik_pegawai,
                            'keterangan_pembayaran' => $data->keterangan_spp,
                            'npwp' => $dat->npwp_pegawai,
                            'alamat' => $dat->alamat,
                            'jumlah_ditransfer' => $dat->jumlah_ditransfer,
                            'asn_type' => 'ASN',
                            'kode_wilayah'  => '21.05',
                            'tx_partner_id'  => $tx_partner_id,
                            'total_data'  => 1,
                            'tx_type' => $tx_type,
                            'status_code' => 0
                        ]);
                    }

                }
            }

            $data->transaksi = DB::table('ta_transaksi')->where('id_spp',$data->id_spp)->get();
            $data->terlampir = !$is_rek;
        }
        return response()->json([
           'success' => $success,
           'message' => $message,
           'data'  => $data,
           'sync_gaji'  => $sync_gaji,
        ], 200);
    }

    static function UploadDokumen($id_spp,$req){
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
              'file'   => 'required|file|max:10240',
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
          DB::table('ta_spp_dokumen')->insert([
              'slug'  => $slug,
              'id_spp'  => $id_spp,
              'id_ref'  => $req->id_ref,
              'uraian'  => $req->uraian,
              'file'  => $file,
              'created_by'  => $nip,
              'updated_by'  => $nip,
          ]);

          $message = 'Tambah Data Berhasil';
          $success = true;
          $data = DB::table('ta_spp_dokumen')->where('id_spp',$id_spp)->get();
          return response()->json([
              'success' => $success,
              'message' => $message,
              'data'  => $data,
          ], 200);
    }

    static function GetDokumen($id_spp){
        $message = 'Sukses Get Data';
        $success = true;
        $data = DB::table('ta_spp_dokumen')->where('id_spp',$id_spp)->get();
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
        ], 200);
    }

    static function FinalBendahara($req){
        $message = 'Sukses Update Data';
        $success = true;


        // return response()->json([
        //     'success' => false,
        //     'message' => 'Gagal Untuk Testing',
        //     'asis_final_bend' => 0,
        // ], 200);

        $data = DB::table('ta_spp')->where('id_spp',$req->id_spp)->update([
            'asis_final_bend' => 1,
            'asis_final_veri' => 0,
        ]);

        // untuk bridging singkron sipd
        DB::table('ta_singkron')->insert([
            'act' => 'spm',
            'act_id'  => 0,
            'status'  => 0,
        ]);

        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
            'asis_final_bend' => 1,
        ], 200);
    }

    static function AddTransaksi($req,$id_spp){
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

        $nomor  = DB::table('ta_transaksi')->where('id_spp',$id_spp)->count();
        $tx_partner_id  = bin2hex(random_bytes(12));
        $spp  = SPP::where('id_spp',$id_spp)->first();
        $tx_type  = 'SP2D|'.$spp->jenis_spp.'|'.strtoupper($spp->jenis_ls_spp);
        DB::table('ta_transaksi')->insert([
            'record_no' => $nomor++,
            'id_spp'  => $id_spp,
            'tx_partner_id'  => $tx_partner_id,
            'account_bank'  => $req->account_bank,
            'rekanan_nama_rekening'  => $req->rekanan_nama_rekening,
            'rekanan_nomor_rekening'  => $req->rekanan_nomor_rekening,
            'rekanan_nik'  => $req->rekanan_nik,
            'kode_wilayah'  => '21.05',
            'total_data'  => 1,
            'tx_type' => $tx_type,
            'status_code' => 0,
            'keterangan_pembayaran' => $spp->keterangan_spp,
        ]);

        $message = 'Sukses Tambah Data';
        $success = true;
        $data = DB::table('ta_transaksi')->where('id_spp',$id_spp)->get();
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'  => $data,
        ], 200);


    }

    static function DeleteTransaksi($req,$id){
        DB::table('ta_transaksi')->where('id',$req->id)->where('id_spp',$req->id_spp)->where('rekanan_nomor_rekening',$req->rekanan_nomor_rekening)->delete();

        $message = 'Sukses Delete Data';
        $success = true;
        $data = DB::table('ta_transaksi')->where('id_spp',$req->id_spp)->get();
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
