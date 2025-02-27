<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;


class Ta_Transaksi extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'ta_transaksi';
    protected $guarded = [];

    public function spm()
    {
        return $this->belongsTo(SPM::class,'id_spp','id_spp');
    }

    public function cek_rekanan(){
        $success  = true; $message = "Data Rekanan Ditemukan"; $sync_rekanan = false;
        $rekanan_nomor_rekening = $this->rekanan_nomor_rekening;
        $rekanan  = Ta_Rekanan::where('nomor_rekening',$rekanan_nomor_rekening)->first();
        if($rekanan){
             $ref_kdbank  = DB::table('ref_kd_bank')->where('id_bank',$rekanan->id_bank)->first();
             if(!$ref_kdbank){
                 $success = false;
                 $message = "Data Kode Bank Tidak Ditemukan, Hubungi Admin";
                 DB::table('ref_kd_bank')->insert([
                     'id_bank' => $rekanan->id_bank,
                     'nama_bank' => $rekanan->nama_bank,
                     'kd_bank' => 0,
                 ]);
             }else{

                 if(!$ref_kdbank->kd_bank){
                      $success = false;
                      $message = "Data Kode Bank Tidak Ditemukan, Hubungi Admin";
                 }else{
                      Ta_Transaksi::where('id',$this->id)->update([
                          'validasi_nomor_rekening' => 1,
                          'account_bank' => $ref_kdbank->kd_bank,
                      ]);
                      $success = true;
                 }
             }
        }else{
             $success = false;
             $message = "Sedang Singkron Data Rekanan, Silahkan Ulangi Kembali";
             $sync_rekanan = true;
        }

        $this->rekanan = [
            "id_trans"  => $this->id,
            "success" => $success,
            "message" => $message,
            "sync_rekanan"  => $sync_rekanan
        ];
        return $this->rekanan;
    }

    public function cek_status()
    {
        if($this->status_code == '999'){
            $btn = 'bg-danger';
            $status_message = 'Data Belum Diproses';
            $btn_disable = false;
        }
        elseif($this->status_code == '000'){
            $btn = 'bg-success';
            $status_message = $this->status_message;
            $btn_disable = true;
        }
        elseif($this->status_code == '905'){
            $btn = 'bg-danger';
            $status_message = $this->status_message;
            $btn_disable = true;
        }
        else{
            $btn = 'bg-danger';
            $status_message = $this->status_message;
            $btn_disable = true;
        }

        $this->infobank = array(
            "btn_disable" => $btn_disable,
            "status_message" => $status_message,
            "btn" => $btn,
        );
        return $this->infobank;
    }

}
