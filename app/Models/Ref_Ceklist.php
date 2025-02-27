<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// use

class Ref_Ceklist extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'ref_ceklist';
    protected $guarded = [];
    protected $hidden = [
       // 'id',
    ];
}
