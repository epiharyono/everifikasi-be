<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// use

class Ta_Histori extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'ta_histori';
    protected $guarded = [];
}
