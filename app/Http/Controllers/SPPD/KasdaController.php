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
use Carbon\Carbon;

use App\Models\User;
use App\Models\SPPD;
use App\Models\OPD;
use App\Models\Ta_Kasda;
use App\Models\Ta_Kasda_Potongan as KASDA_POT;
use App\Models\Ta_Kasda_KDPotongan as KASDA_KDPOT;
use App\Models\SPP_CETAK;
use App\Models\SPM;
use App\Models\SPM_POTONGAN;


use App\Http\Controllers\Users\HakAksesController as HALocal;
use App\Http\Controllers\JwtController as JWT;
use App\Http\Controllers\ApiAsis;

class KasdaController extends Controller
{

   static function FinalSPPD($req){
        $success = false; $message = 'Otoritas Tidak Diizinkan';
        $super   = 1; $data = '';  $bersih = 0;
        $token = $req->bearerToken();

        $user  = JWT::CheckJWT($token);
        if(!$user['success']){
           return response()->json(['success' => false,'message' => $message], 401);
        }
        $nip  = $user['data']['nip'];
        $name  = $user['data']['name'];


        $payload = $req->all();
        $sp2d  = SPPD::where('id_sp_2_d',$req->id_sp_2_d)->with('spm')->with('cetak')->with('potongan')->with('rekening')->first();
        if(!$sp2d){
            return response()->json([
                'success' => false,
                'message' => 'SP2D Tidak Ditemukan'
            ], 200);
        }
        $sp2d->kd_opd = DB::table('ta_kasda_kdopd')->where('id_skpd',$sp2d->id_sub_skpd)->first();
        $sp2d->spm->DataSPP();

        if(!$sp2d->kd_opd){
            return response()->json([
                'success' => false,
                'message' => 'Kode OPD Tidak Ditemukan',
                'id_skpd'  => $sp2d->id_skpd,
                'nama'  => $sp2d->nama_sub_skpd,
                'sp2d'  => $sp2d,
                'payload'  => $payload,
            ], 200);
        }
        $asis_final_veri = 1;

        if(sizeOf($sp2d->potongan)){
            foreach($sp2d->potongan as $dpot){
                $cek  = KASDA_KDPOT::where('id_pajak_potongan',$dpot->id_pajak_potongan)->first();
                if(!$cek){
                    return response()->json([
                        'success' => false,
                        'message' => 'Kode Rekening Potongan Tidak Ditemukan ('.$dpot->id_pajak_potongan.' - '.$dpot->nama_pajak_potongan.')',
                    ], 200);
                }
            }
        }

        if($req->status == 1){
            $message = 'SP2D Berhasil Diproses';
            if($sp2d->jenis_sp_2_d == 'GU'){
                $message = 'SP2D GU Berhasil Diproses';
                $spp_cetak = SPP_CETAK::where('id_spp',$sp2d->spm->id_spp)->first();
                $cek  =  Ta_Kasda::where('id_sp2d',$sp2d->id_sp_2_d)->first();
                if(!$cek){
                    $kd  = $sp2d->kd_opd;
                    $sp2d_cetak  = $sp2d->cetak;
                    $now  = date('Y-m-d');
                    $bersih = $sp2d_cetak->nilai_sp2d;

                    DB::beginTransaction();
                    try {
                        Ta_Kasda::create([
                            'id_sp2d' => $sp2d->id_sp_2_d,
                            'Kd_Urusan' => $kd->kd_urusan,
                            'Kd_Bidang' => $kd->kd_bidang,
                            'Kd_Unit' => $kd->kd_unit,
                            'Kd_Sub' => $kd->kd_sub,
                            'Tahun' => $sp2d->tahun,
                            'No_SP2D' => $sp2d->nomor_sp_2_d,
                            'No_SPM' => $sp2d->spm->nomor_spm,
                            'Tgl_SP2D' => Carbon::parse($sp2d_cetak->tanggal_sp_2_d)->toDateTimeString(),
                            'Tgl_SPM' => Carbon::parse($sp2d->spm->tanggal_spm)->toDateTimeString(),
                            'Jn_SPM' => $sp2d->spm->jenis_spm,
                            'Nm_Penerima' => $spp_cetak->nama_rek_bp_bpp,
                            'Keterangan' => $sp2d->keterangan_sp_2_d,
                            'NPWP' => $spp_cetak->npwp_bp_bpp,
                            'Bank_Penerima' => $spp_cetak->bank_bp_bpp,
                            'Rek_Penerima' => $spp_cetak->no_rek_bp_bpp,
                            'Tgl_Penguji' => $now,
                            'Nm_Bank' => 'PEMERINTAH KAB.KEP.ANAMBAS (RIAU KEPRI)',
                            'No_Rekening' => $sp2d_cetak->nomor_rekening,
                            'Nilai' => $bersih,
                            'DateCreate' => $now,
                            'Cair' => 0,
                            'nip' => $nip
                        ]);
                        SPPD::where('id_sp_2_d',$req->id_sp_2_d)->update([
                            'asis_final'  => 1
                        ]);
                        DB::commit();
                        $success = true;
                    }catch (\Exception $e) {
                        DB::rollback();
                        $message   = $e->getMessage();
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                        ], 200);
                    }

                }else{
                    return response()->json([
                        'success' => false,
                        'message' => 'SP2D Ini sudah diproses',
                    ], 200);
                }

            }else{
                $cek  =  Ta_Kasda::where('id_sp2d',$sp2d->id_sp_2_d)->first();
                if(!$cek){
                    $kd  = $sp2d->kd_opd;
                    $sp2d_cetak  = $sp2d->cetak;
                    $now  = date('Y-m-d');
                    $bruto = $sp2d_cetak->nilai_sp2d;

                    $pot  = 0;
                    foreach($sp2d->potongan as $dat){
                        $pot += $dat->nilai_spp_pajak_potongan;
                    }
                    $bersih = $bruto - $pot;

                    DB::beginTransaction();
                    try {
                        Ta_Kasda::create([
                            'id_sp2d' => $sp2d->id_sp_2_d,
                            'Kd_Urusan' => $kd->kd_urusan,
                            'Kd_Bidang' => $kd->kd_bidang,
                            'Kd_Unit' => $kd->kd_unit,
                            'Kd_Sub' => $kd->kd_sub,
                            'Tahun' => $sp2d->tahun,
                            'No_SP2D' => $sp2d->nomor_sp_2_d,
                            'No_SPM' => $sp2d->spm->nomor_spm,
                            'Tgl_SP2D' => Carbon::parse($sp2d_cetak->tanggal_sp_2_d)->toDateTimeString(),
                            'Tgl_SPM' => Carbon::parse($sp2d->spm->tanggal_spm)->toDateTimeString(),
                            'Jn_SPM' => $sp2d->spm->jenis_spm,
                            'Nm_Penerima' => $sp2d_cetak->nama_pihak_ketiga,
                            'Keterangan' => $sp2d->keterangan_sp_2_d,
                            'NPWP' => $sp2d_cetak->npwp_pihak_ketiga,
                            'Bank_Penerima' => $sp2d_cetak->bank_pihak_ketiga,
                            'Rek_Penerima' => $sp2d_cetak->no_rek_pihak_ketiga,
                            'Tgl_Penguji' => $now,
                            'Nm_Bank' => 'PEMERINTAH KAB.KEP.ANAMBAS (RIAU KEPRI)',
                            'No_Rekening' => $sp2d_cetak->nomor_rekening,
                            'Nilai' => $bersih,
                            'DateCreate' => $now,
                            'Cair' => 0,
                            'nip' => $nip
                        ]);

                        if(sizeOf($sp2d->potongan)){
                            foreach($sp2d->potongan as $dpot){
                                $cek  = KASDA_KDPOT::where('id_pajak_potongan',$dpot->id_pajak_potongan)->first();
                                $exp  = explode('.',$cek->kode_rek);
                                KASDA_POT::create([
                                    "id_sp2d" => $sp2d->id_sp_2_d,
                                    'Tahun' => $sp2d->tahun,
                                    'Kd_Urusan' => $kd->kd_urusan,
                                    'Kd_Bidang' => $kd->kd_bidang,
                                    'Kd_Unit' => $kd->kd_unit,
                                    'Kd_Sub' => $kd->kd_sub,
                                    'No_SP2D' => $sp2d->nomor_sp_2_d,
                                    'No_SPM' => $sp2d->spm->nomor_spm,
                                    "Kd_Rek_1" => $exp[0],
                                    "Kd_Rek_2" => $exp[1],
                                    "Kd_Rek_3" => $exp[2],
                                    "Kd_Rek_4" => $exp[3],
                                    "Kd_Rek_5" => $exp[4],
                                    'Jn_SPM' => $sp2d->spm->jenis_spm,
                                    "Nm_Rekening" => $dpot->nama_pajak_potongan,
                                    "Nilai" => $dpot->nilai_spp_pajak_potongan,
                                    "nip" => $nip
                                ]);

                            }
                        }
                        SPPD::where('id_sp_2_d',$req->id_sp_2_d)->update([
                            'asis_final'  => 1
                        ]);
                        DB::commit();
                        $success = true;
                        $message = 'Sukses';
                    }catch (\Exception $e) {
                        DB::rollback();
                        $message   = $e->getMessage();
                        return response()->json([
                            'success' => false,
                            'message' => $message,
                        ], 200);
                    }

                }else{
                    return response()->json([
                        'success' => false,
                        'message' => 'SP2D Ini sudah diproses',
                    ], 200);
                }
            }
        }
        elseif($req->status == 3){
            SPM::where('id_spm',$sp2d->spm->id_spm)->update([
                'asis_final_veri'  => 0,
            ]);
            $asis_final_veri = 3;
            $message = 'Data Berhasil Ditolak';
            $success = true;
        }

        if($success){
            try {
                // INI UNTUK NOTIF KE BENDAHARA
                $error = '';
                $nip_bend  = $sp2d->spm->spp['nip_bend'];
                // $token = 'eyJhbGciOiJIUzI1NiIsImtpZCI6ImdseGJpSVVZTVhTM0ZPakxrMHNBZHRJWmZGYk9Zc3NaIiwidHlwIjoiSldUIn0.eyJleHAiOjE3MzA4OTYxOTQsImlkIjoxLCJuYW1lIjoiRVBJIEhBUllPTk8iLCJuaWsiOiIiLCJuaXAiOiIxOTg0MDkwODIwMTIxMjEwMDEiLCJzdWIiOjF9.mynYsfk89EPuMw_C1ckp3SOU6VzKUiPvtRPWPW1KPfk';
                $user_api = ApiAsis::GetHtHUsers($nip_bend);
                if($user_api->success){
                    $hp = $user_api->data->hp;
                    if($req->status == 1) $status  = 'Pengajuan Anda Saat ini sudah terkirim di Bank';
                    else $status = 'Status Pengajuan Anda ditolak';
                    $kirim_pesan = '*Informasi Pengajuan*';
                    $kirim_pesan .= ' \n\n'.'*'.$sp2d->nomor_sp_2_d.'*';
                    $kirim_pesan .= ' \n\n'.$status.', Terimakasih';
                    ApiAsis::SendMessage($hp,$kirim_pesan);
                }

                // INI UNTUK NOTIF KE PETUGAS SP2D
                if($req->status == 1) $status  = 'Saat ini sudah terkirim di Bank';
                else $status = 'Status Pengajuan ditolak';
                $kirim_pesan = '*Informasi Pengajuan*';
                $kirim_pesan .= ' \n\n'.'*'.$sp2d->nomor_sp_2_d.'*';
                $kirim_pesan .= ' \n\n'.$status.', Terimakasih';
                $tim_sp2d  = DB::table('ta_otoritas')->where('id_ref',4)->where('lihat',1)->get();
                foreach($tim_sp2d as $dat){
                    $user_api = ApiAsis::GetHtHUsers($dat->nip);
                    if($user_api->success){
                        $hp = $user_api->data->hp;
                        ApiAsis::SendMessage($hp,$kirim_pesan);
                    }
                }

                // INI UNTUK NOTIF KE BANK
                if($req->status == 1){
                    $kirim_pesan = '*Informasi Pengajuan*';
                    $kirim_pesan .= ' \n\n'.'*'.$sp2d->nomor_sp_2_d.'*';
                    $kirim_pesan .= ' \n\nMohon segera diproses.';
                    $kirim_pesan .= ' \n'.$name;
                    $kirim_pesan .= ' \nTerimakasih';
                    $tim_sp2d  = DB::table('ta_otoritas')->where('id_ref',5)->where('lihat',1)->get();
                    foreach($tim_sp2d as $dat){
                        $user_api = ApiAsis::GetHtHUsers($dat->nip);
                        if($user_api->success){
                            $hp = $user_api->data->hp;
                            ApiAsis::SendMessage($hp,$kirim_pesan);
                        }
                    }
                }
                $message = $kirim_pesan;
            }catch (\Exception $e) {
                $message = 'Transaksi Sukses !!!';
                $error = $e->getMessage();
            }

        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'asis_final_veri' => $asis_final_veri,
            'bersih'  => $bersih,
            'sp2d'  => $sp2d,
            'payload'  => $user,
            'error' => $error,
        ], 200);

    }

}
