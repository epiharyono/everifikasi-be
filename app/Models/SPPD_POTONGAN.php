<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class SPPD_POTONGAN extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'ta_sppd_potongan';
    protected $guarded = [];

    public function sppd()
    {
        return $this->belongsTo(SPPD_CETAK::class,'id_sp_2_d','id_sp_2_d');
    }

    public function is_pajak()
    {
        if($this->id_pajak_potongan < 12){
            $btn  = false;
        }else{
            $btn  = true;
        }
        $this->btn_disable = $btn;
        return $this->btn_disable;

    }

    public function nilai_sp2d_pajak_potongan(){
        $this->nominal = "".$this->nominal.".00";
        return $this->nominal;
    }

    public function nama_potongan(){
        $cek  = DB::table('mapping_potongan')->where('id',$this->id)->first();
        if($cek){
            $this->nama_potongan = $cek->nm_mapping;
        }else{
            $this->nama_potongan = $this->nama_potongan;
        }
        return $this->nama_potongan;
    }
}
