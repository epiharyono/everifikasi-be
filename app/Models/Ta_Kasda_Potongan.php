<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// use

class Ta_Kasda_Potongan extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'ta_kasda_potongan';
    protected $guarded = [];
    protected $hidden = [
       'id',
       // 'created_at', 'updated_at', 'nip', 'id_sp2d'
    ];
}
