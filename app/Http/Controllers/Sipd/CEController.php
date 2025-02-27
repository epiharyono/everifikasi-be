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
use App\Models\SPPD;
// use App\Models\GAJI;
// use App\Models\DPA;
// use App\Models\DPA_REKENING;

use App\Http\Controllers\Users\DataController as DATA;
use App\Http\Controllers\Users\UserController as Userc;

use GuzzleHttp\Client as GuzzleHttpClient;

class CEController extends Controller
{

    static function SingSP2D($req){
        $message = 'Sing SP2D';
        $data = $req->data;
        $data = json_decode($data, true);
        $datas =  '';
        if(sizeOf($data)){
            $message = 'oke size';

            foreach($data as $dat){
                $datas =  $dat['keterangan_sp_2_d'];
                // $tanggal_spp = date("Y-m-d H:i:s", strtotime($dat->tanggal_spp));
                // $verifikasi_spp_at = date("Y-m-d H:i:s", strtotime($dat->verifikasi_spp_at));
                // $status_perubahan_at = date("Y-m-d H:i:s", strtotime($dat->status_perubahan_at));
                // $updated_at = date("Y-m-d H:i:s", strtotime($dat->updated_at));
                // $created_at = date("Y-m-d H:i:s", strtotime($dat->created_at));
                // $created_at = date("Y-m-d H:i:s", strtotime($dat->created_at));
                $cek   = SPPD::where('id_sp_2_d',$dat['id_sp_2_d'])->first();
                if(!$cek){
                    SPPD::create([
                      'id_sp_2_d' => $dat['id_sp_2_d'],
                      'nomor_sp_2_d' => $dat['nomor_sp_2_d'],
                      'id_spm' => $dat['id_spm'],
                      'nomor_spm' => $dat['nomor_spm'],
                      'tanggal_spm' => $dat['tanggal_spm'],
                      'tahun' => $dat['tahun'],
                      'id_daerah' => $dat['id_daerah'],
                      'id_unit' => $dat['id_unit'],
                      'id_skpd' => $dat['id_skpd'],
                      'kode_skpd' => $dat['kode_skpd'],
                      'nama_skpd' => $dat['nama_skpd'],
                      'id_sub_skpd' => $dat['id_sub_skpd'],
                      'kode_sub_skpd' => $dat['kode_sub_skpd'],
                      'nama_sub_skpd' => $dat['nama_sub_skpd'],
                      'nilai_sp_2_d' => $dat['nilai_sp_2_d'],
                      'nilai_materai_sp_2_d' => $dat['nilai_materai_sp_2_d'],
                      'tanggal_sp_2_d' => $dat['tanggal_sp_2_d'],
                      'keterangan_sp_2_d' => $dat['keterangan_sp_2_d'],
                      'is_verifikasi_sp_2_d' => $dat['is_verifikasi_sp_2_d'],
                      'verifikasi_sp_2_d_by' => $dat['verifikasi_sp_2_d_by'],
                      'verifikasi_sp_2_d_at' => $dat['verifikasi_sp_2_d_at'],
                      'keterangan_verifikasi_sp_2_d' => $dat['keterangan_verifikasi_sp_2_d'],
                      'is_transfer_sp_2_d' => $dat['is_transfer_sp_2_d'],
                      'transfer_sp_2_d_by' => $dat['transfer_sp_2_d_by'],
                      'transfer_sp_2_d_at' => $dat['transfer_sp_2_d_at'],
                      'keterangan_transfer_sp_2_d' => $dat['keterangan_transfer_sp_2_d'],
                      'jenis_sp_2_d' => $dat['jenis_sp_2_d'],
                      'jenis_ls_sp_2_d' => $dat['jenis_ls_sp_2_d'],
                      'is_kunci_rekening_sp_2_d' => $dat['is_kunci_rekening_sp_2_d'],
                      'is_gaji' => $dat['is_gaji'],
                      'jenis_gaji' => $dat['jenis_gaji'],
                      'bulan_gaji' => $dat['bulan_gaji'],
                      'tahun_gaji' => $dat['tahun_gaji'],
                      'is_tpp' => $dat['is_tpp'],
                      'bulan_tpp' => $dat['bulan_tpp'],
                      'tahun_tpp' => $dat['tahun_tpp'],
                      'is_pelimpahan' => $dat['is_pelimpahan'],
                      'id_pegawai_bud_kbud' => $dat['id_pegawai_bud_kbud'],
                      'nama_bud_kbud' => $dat['nama_bud_kbud'],
                      'nip_bud_kbud' => $dat['nip_bud_kbud'],
                      'id_rkud' => $dat['id_rkud'],
                      'jenis_rkud' => $dat['jenis_rkud'],
                      'no_rek_bp_bpp' => $dat['no_rek_bp_bpp'],
                      'nama_rek_bp_bpp' => $dat['nama_rek_bp_bpp'],
                      'id_bank' => $dat['id_bank'],
                      'nama_bank' => $dat['nama_bank'],
                      'id_sumber_dana' => $dat['id_sumber_dana'],
                      'is_status_perubahan' => $dat['is_status_perubahan'],
                      'status_perubahan_at' => $dat['status_perubahan_at'],
                      'status_perubahan_by' => $dat['status_perubahan_by'],
                      'status_aklap' => $dat['status_aklap'],
                      'nomor_jurnal' => $dat['nomor_jurnal'],
                      'jurnal_id' => $dat['jurnal_id'],
                      'metode' => $dat['metode'],
                      'id_jadwal' => $dat['id_jadwal'],
                      'id_tahap' => $dat['id_tahap'],
                      'status_tahap' => $dat['status_tahap'],
                      'kode_tahap' => $dat['kode_tahap'],
                      'created_by' => $dat['created_by'],
                      'updated_by' => $dat['updated_by'],
                      'deleted_by' => $dat['deleted_by'],
                      // 'overbook_gagal' => $dat['overbook_gagal'],
                      // 'overbook_expired' => $dat['overbook_expired'],
                      // 'execution_time' => $dat['execution_time'],
                      'created_at' => $dat['created_at'],
                      'updated_at' => $dat['updated_at'],
                    ]);
                }
            }
        }
        return response()->json([
            'success' => false,
            'status'  => 'success',
            'message' => $message,
            'data'  => $datas,
            'payload' => $req->all(),
        ], 200);

    }

    static function SingSP2DDetail($req){
        $message = 'Sing SingSP2DDetail';
        $header = '';
        $data = $req->data;
        $data = json_decode($data, true);
        $id   = $req->id_sp_2_d;
        try {
            if(sizeOf($data)){
                $jenis = $req->jenis;
                // $data = $data['jenis'];
                if($jenis == 'LS'){
                    $header  = $data['header'];
                    // $header = $header['tahun'];
                    $cek  = DB::table('ta_sppd_cetak')->where('id_sp_2_d',$id)->first();
                    if($cek){
                        DB::table('ta_sppd_cetak')->where('id_sp_2_d',$id)->update([
                            'tahun' => $header['tahun'],
                            'nomor_rekening' => $header['nomor_rekening'],
                            'nama_bank' => $header['nama_bank'],
                            'nomor_sp_2_d' => $header['nomor_sp_2_d'],
                            'tanggal_sp_2_d' => $header['tanggal_sp_2_d'],
                            'nama_skpd' => $header['nama_skpd'],
                            'nama_sub_skpd' => $header['nama_sub_skpd'],
                            'nama_pihak_ketiga' => $header['nama_pihak_ketiga'],
                            'no_rek_pihak_ketiga' => $header['no_rek_pihak_ketiga'],
                            'nama_rek_pihak_ketiga' => $header['nama_rek_pihak_ketiga'],
                            'bank_pihak_ketiga' => $header['bank_pihak_ketiga'],
                            'npwp_pihak_ketiga' => $header['npwp_pihak_ketiga'],
                            'keterangan_sp2d' => $header['keterangan_sp2d'],
                            'nilai_sp2d' => $header['nilai_sp2d'],
                            'cabang_bank' => $header['cabang_bank'],
                            'nomor_spm' => $header['nomor_spm'],
                            'tanggal_spm' => $header['tanggal_spm'],
                            'nama_ibu_kota' => $header['nama_ibu_kota'],
                            'nama_bud_kbud' => $header['nama_bud_kbud'],
                            'nip_bud_kbud' => $header['nip_bud_kbud'],
                            'jabatan_bud_kbud' => $header['jabatan_bud_kbud'],
                        ]);
                    }
                    else{
                        DB::table('ta_sppd_cetak')->insert([
                            'id_sp_2_d'  => $id,
                            'tahun' => $header['tahun'],
                            'nomor_rekening' => $header['nomor_rekening'],
                            'nama_bank' => $header['nama_bank'],
                            'nomor_sp_2_d' => $header['nomor_sp_2_d'],
                            'tanggal_sp_2_d' => $header['tanggal_sp_2_d'],
                            'nama_skpd' => $header['nama_skpd'],
                            'nama_sub_skpd' => $header['nama_sub_skpd'],
                            'nama_pihak_ketiga' => $header['nama_pihak_ketiga'],
                            'no_rek_pihak_ketiga' => $header['no_rek_pihak_ketiga'],
                            'nama_rek_pihak_ketiga' => $header['nama_rek_pihak_ketiga'],
                            'bank_pihak_ketiga' => $header['bank_pihak_ketiga'],
                            'npwp_pihak_ketiga' => $header['npwp_pihak_ketiga'],
                            'keterangan_sp2d' => $header['keterangan_sp2d'],
                            'nilai_sp2d' => $header['nilai_sp2d'],
                            'cabang_bank' => $header['cabang_bank'],
                            'nomor_spm' => $header['nomor_spm'],
                            'tanggal_spm' => $header['tanggal_spm'],
                            'nama_ibu_kota' => $header['nama_ibu_kota'],
                            'nama_bud_kbud' => $header['nama_bud_kbud'],
                            'nip_bud_kbud' => $header['nip_bud_kbud'],
                            'jabatan_bud_kbud' => $header['jabatan_bud_kbud'],
                        ]);
                    }
                    $detail  = $data['detail_belanja'];
                    DB::table('ta_sppd_rekening')->where('id_sp_2_d',$id)->delete();
                    foreach($detail as $dat){
                        if($dat['kode_rekening'] != ''){
                            DB::table('ta_sppd_rekening')->insert([
                                'id_sp_2_d'  => $id,
                                'kode_rekening' => $dat['kode_rekening'],
                                'uraian' => $dat['uraian'],
                                'total_anggaran' => $dat['total_anggaran'],
                                'jumlah' => $dat['jumlah'],
                            ]);
                        }
                    }
                    $potongan  = $data['pajak_potongan'];
                    DB::table('ta_sppd_potongan')->where('id_sp_2_d',$id)->delete();
                    if($potongan){
                        foreach($potongan as $dat){
                            DB::table('ta_sppd_potongan')->insert([
                                'id_sp_2_d'  => $id,
                                'id_pajak_potongan' => $dat['id_pajak_potongan'],
                                'nama_pajak_potongan' => $dat['nama_pajak_potongan'],
                                'kode_sinergi' => $dat['kode_sinergi'],
                                'nama_sinergi' => $dat['nama_sinergi'],
                                'id_billing' => $dat['id_billing'],
                                'nilai_sp2d_pajak_potongan' => $dat['nilai_sp2d_pajak_potongan'],
                            ]);
                        }
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
            'payload' => $req->jenis,
        ], 200);
    }

}
