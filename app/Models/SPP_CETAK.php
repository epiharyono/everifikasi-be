<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// use

class SPP_CETAK extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'ta_spp_cetak';
    protected $guarded = [];

    public function sppd()
    {
        return $this->belongsTo(SPPD::class,'id_sp_2_d','id_sp_2_d');
    }
}
