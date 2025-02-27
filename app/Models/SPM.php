<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class SPM extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'ta_spm';
    protected $guarded = [];

    public function sppd()
    {
        return $this->belongsTo(SPPD::class,'id_spm','id_spm');
    }

    public function spm_cetak()
    {
        return $this->belongsTo(SPM_CETAK::class,'id_spm','id_spm');
    }

    public function verified()
    {
        $id_spm  = $this->id_spm;
        $data = SPM_VERIFIED::where('id_spm',$id_spm)->first();
        if($data){
            $this->info_verified = [
                'name'  => $data->name,
                'nip' => $data->nip,
                'created_at' => $data->created_at,
                'desc'  => 'Sedang Proses Verifikasi Oleh '.$data->name
            ];
        }else{
            $this->info_verified = [
                'name'  => 'Belum Diproses',
                'nip' => 0,
                'created_at' => '000',
                'desc'  => 'Belum Proses Verifikasi'
            ];
        }
        return $this->info_verified;
    }

    public function pajak_potongan()
    {
        return $this->hasMany(SPM_POTONGAN::class,'id_spm','id_spm');
    }

    public function rekening()
    {
        return $this->hasMany(SPM_REKENING::class,'id_spm','id_spm');
    }

    public function dokumen()
    {
        return $this->hasMany(SPM_DOKUMEN::class,'id_spm','id_spm');
    }

    public function Cek_Ceklist(){
        $id_spm = $this->id_spm;
        $jenis  = $this->jenis_spm;
        $valid_bend = $this->asis_final_bend;
        if(!$valid_bend){
            if($jenis == 'LS'){
                $ref  = Ref_Ceklist::where('jenis_spm','LS')->get();
            }else{
                $ref  = Ref_Ceklist::where('jenis_spm','GU')->get();
            }
            foreach($ref as $dat){
              $cek = Ta_Ceklist::where('ref_id',$dat->id)->where('id_spm',$id_spm)->first();
              if(!$cek){
                  Ta_Ceklist::create([
                      'ref_id'  => $dat->id,
                      'id_spm'  => $id_spm,
                      'uraian'  => $dat->uraian,
                      'created_by' => $this->created_by,
                      'updated_by'  => $this->created_by,
                  ]);
              }else{
                  Ta_Ceklist::where('id',$cek->id)->update([
                      'uraian'  => $dat->uraian,
                      'updated_by'  => $this->created_by,
                  ]);
              }
            }
        }
        $this->cek_list = Ta_Ceklist::where('id_spm',$id_spm)->get();
        return $this->cek_list;
    }

    public function DataSPP(){
        $nip_bend = 0;
        $spp  = SPP::where('id_spp',$this->id_spp)->first();
        $cetak  = SPP_CETAK::where('id_spp',$this->id_spp)->first();
        if($cetak){
            $nip_bend  = $cetak->nip_bp_bpp;
        }
        $this->spp = [
            'spp' => $spp,
            'spp_cetak' => $cetak,
            'nip_bend'  => $nip_bend,
        ];
        return $this->spp;
    }

    public function transaksi(){
        $id_spm = $this->id_spm;
        $id_spp = $this->id_spp;
        $sync_gaji = false;
        $spp  = SPP::where('id_spp',$id_spp)->first();
        if(!$spp){
            
        }

        if(isset($spp->rekanan_nomor_rekening)) $is_rek  = is_numeric($spp->rekanan_nomor_rekening);
        else $is_rek = false;

        if($spp->jenis_spp == 'LS' && $spp->bulan_gaji == 0 && $is_rek == true){
            $cek  = Ta_Transaksi::where('id_spm',$id_spm)->first();
            if(!$cek){
                $tx_partner_id = bin2hex(random_bytes(12));
                $tx_type  = 'SP2D|'.$spp->jenis_spp.'|'.strtoupper($spp->jenis_ls_spp);
                Ta_Transaksi::insert([
                    'id_spm'  => $id_spm,
                    'record_no' => 1,
                    'account_bank'  => 119,
                    'rekanan_nama_rekening' => $spp->rekanan_nama_rekening,
                    'rekanan_nomor_rekening' => $spp->rekanan_nomor_rekening,
                    'rekanan_nik' => $spp->rekanan_nik,
                    'keterangan_pembayaran' => $spp->keterangan_spp,
                    'asn_type' => 'rekanan',
                    'kode_wilayah'  => '21.05',
                    'tx_partner_id'  => $tx_partner_id,
                    'total_data'  => 1,
                    'tx_type' => $tx_type,
                    'status_code' => 0
                ]);
            }
        }
        elseif($spp->is_gaji){
            $cek  = Ta_Transaksi::where('id_spm',$id_spm)->count();
            if(!$cek){
                $record = 0;
                $gaji = DB::table('ta_gaji')->where('id_skpd',$spp->id_skpd)->where('bulan_gaji',$spp->bulan_gaji)->get();
                if(!sizeOf($gaji)){
                    $sync_gaji = true;
                }

                $pot = DB::table('ta_gaji')->where('jenis_gaji',1)->where('bulan_gaji',$spp->bulan_gaji)->where('id_skpd',$spp->id_skpd)->sum('jumlah_potongan');
                $tf  = DB::table('ta_gaji')->where('jenis_gaji',1)->where('bulan_gaji',$spp->bulan_gaji)->where('id_skpd',$spp->id_skpd)->sum('jumlah_ditransfer');
                $total = $pot + $tf;
                if($total == $spp->nilai_spp){
                      $gaji = DB::table('ta_gaji')->where('jenis_gaji',1)->where('id_skpd',$spp->id_skpd)->where('bulan_gaji',$spp->bulan_gaji)->get();
                      foreach($gaji as $dat){
                          $tx_partner_id = bin2hex(random_bytes(12));
                          $tx_type  = 'SP2D|'.$spp->jenis_spp.'|'.strtoupper($spp->jenis_ls_spp);
                          $cek  = Ta_Transaksi::where('id_gaji_pegawai',$dat->id_gaji_pegawai)->first();
                          if(!$cek){
                              Ta_Transaksi::insert([
                                  'id_spm'  => $id_spm,
                                  'record_no' => $record++,
                                  'id_gaji_pegawai'  => $dat->id_gaji_pegawai,
                                  'account_bank'  => $dat->kode_bank,
                                  'rekanan_nama_rekening' => $dat->nama_pegawai,
                                  'rekanan_nomor_rekening' => $dat->nomor_rekening_bank_pegawai,
                                  'rekanan_nik' => $dat->nik_pegawai,
                                  'keterangan_pembayaran' => $spp->keterangan_spp,
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
                    $pot = DB::table('ta_gaji')->where('jenis_gaji',2)->where('bulan_gaji',$spp->bulan_gaji)->where('id_skpd',$spp->id_skpd)->sum('jumlah_potongan');
                    $tf  = DB::table('ta_gaji')->where('jenis_gaji',2)->where('bulan_gaji',$spp->bulan_gaji)->where('id_skpd',$spp->id_skpd)->sum('jumlah_ditransfer');
                    $total = $pot + $tf;
                    if($total == $spp->nilai_spp){
                        $gaji = DB::table('ta_gaji')->where('jenis_gaji',2)->where('id_skpd',$spp->id_skpd)->where('bulan_gaji',$spp->bulan_gaji)->get();
                        foreach($gaji as $dat){
                            $tx_partner_id = bin2hex(random_bytes(12));
                            $tx_type  = 'SP2D|'.$spp->jenis_spp.'|'.strtoupper($spp->jenis_ls_spp);
                            $cek  = Ta_Transaksi::where('id_gaji_pegawai',$dat->id_gaji_pegawai)->first();
                            if(!$cek){
                                Ta_Transaksi::insert([
                                    'id_spm'  => $id_spm,
                                    'record_no' => $record++,
                                    'id_gaji_pegawai'  => $dat->id_gaji_pegawai,
                                    'account_bank'  => $dat->kode_bank,
                                    'rekanan_nama_rekening' => $dat->nama_pegawai,
                                    'rekanan_nomor_rekening' => $dat->nomor_rekening_bank_pegawai,
                                    'rekanan_nik' => $dat->nik_pegawai,
                                    'keterangan_pembayaran' => $spp->keterangan_spp,
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
        elseif($spp->is_tpp){
            $record = 0;
            $gaji = DB::table('ta_gaji')->where('id_skpd',$spp->id_skpd)->where('bulan_gaji',$spp->bulan_gaji)->get();
            if(!sizeOf($gaji)){
                $sync_gaji = true;
            }
            $cek  = Ta_Transaksi::where('id_spm',$id_spm)->first();
            if(!$cek){
                foreach($gaji as $dat){
                    $tx_partner_id = bin2hex(random_bytes(12));
                    $tx_type  = 'SP2D|'.$spp->jenis_spp.'|'.strtoupper($spp->jenis_ls_spp);
                    Ta_Transaksi::insert([
                        'id_spm'  => $id_spm,
                        'record_no' => $record++,
                        // 'account_bank'  => $dat->kode_bank,
                        'account_bank'  => '119',
                        'rekanan_nama_rekening' => $dat->nama_pegawai,
                        'rekanan_nomor_rekening' => $dat->nomor_rekening_bank_pegawai,
                        'rekanan_nik' => $dat->nik_pegawai,
                        'keterangan_pembayaran' => $spp->keterangan_spp,
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
        elseif($spp->jenis_spp == 'GU'){
            $spp_cetak  = SPP_CETAK::where('id_spp',$id_spp)->first();
            $cek  = Ta_Transaksi::where('id_spm',$id_spm)->first();
            if(!$cek){
                $tx_partner_id = bin2hex(random_bytes(12));
                $tx_type  = 'SP2D|'.$spp->jenis_spp.'|BENDAHARA';
                Ta_Transaksi::insert([
                    'id_spm'  => $id_spm,
                    'record_no' => 1,
                    'account_bank'  => 119,
                    'rekanan_nama_rekening' => $spp_cetak->nama_rek_bp_bpp,
                    'rekanan_nomor_rekening' => $spp_cetak->no_rek_bp_bpp,
                    'rekanan_nik' => $spp_cetak->nip_bp_bpp,
                    'keterangan_pembayaran' => $spp_cetak->keterangan,
                    'asn_type' => 'rekanan',
                    'kode_wilayah'  => '21.05',
                    'tx_partner_id'  => $tx_partner_id,
                    'total_data'  => 1,
                    'tx_type' => $tx_type,
                    'jumlah_ditransfer' => $spp_cetak->nilai,
                    'status_code' => 0
                ]);
            }
        }

        $this->transaksi = [
            'data'  => Ta_Transaksi::where('id_spm',$id_spm)->get(),
            'sync_gaji'  => $sync_gaji,
        ];
        return $this->transaksi;
    }


}
