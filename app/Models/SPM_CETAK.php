<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SPM_CETAK extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'ta_spm_cetak';
    protected $guarded = [];

    public function spp(){
        $nomor_spp = $this->nomor_spp;
        $this->spp = SPP::where('nomor_spp',$nomor_spp)->first();
        return $this->spp;
    }

    public function spp_cetak(){
        $id_spp = $this->spp->id_spp;
        $this->spp_cetak = SPP_CETAK::where('id_spp',$id_spp)->first();
        return $this->spp_cetak;
    }
}
