<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SPPD extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'ta_sppd';
    protected $guarded = [];

    public function spm()
    {
        return $this->belongsTo(SPM::class,'id_spm','id_spm');
    }

    public function spm_cetak()
    {
        return $this->belongsTo(SPM_CETAK::class,'id_spm','id_spm');
    }

    public function spm_dokumen()
    {
        return $this->hasMany(SPM_DOKUMEN::class,'id_spm','id_spm');
    }

    public function ceklist()
    {
        return $this->hasMany(Ta_Ceklist::class,'id_spm','id_spm');
    }

    public function cetak()
    {
        return $this->belongsTo(SPPD_CETAK::class,'id_sp_2_d','id_sp_2_d');
    }

    public function bank()
    {
        return $this->hasMany(SPPD_BANK::class,'id_sp2d','id_sp_2_d');
    }

    public function rekening()
    {
        return $this->hasMany(SPPD_REKENING::class,'id_sp_2_d','id_sp_2_d');
    }

    public function potongan()
    {
        return $this->hasMany(SPM_POTONGAN::class,'id_spm','id_spm');
    }

    public function getStatus(){
        // bg-info  bg-success  bg-warning
        $id_sp2d  = $this->id_sp_2_d;
        $asis_final  = $this->asis_final;
        $asis_final_bank  = $this->asis_final_bank;

        if($asis_final == 0){
            $this->status_cair = [
                'bg'  => 'bg-danger',
                'desc'  => 'Belum Diverif BUD/KBUD',
                'id'  => $id_sp2d,
            ];
            return $this->status_cair;
        }elseif($asis_final == 2){
            $this->status_cair = [
                'bg'  => 'bg-danger',
                'desc'  => 'Ditolak BUD/KBUD',
                'id'  => $id_sp2d,
            ];
            return $this->status_cair;
        }else{
            $bank     = Ta_Kasda::where('id_sp2d',$id_sp2d)->first();
            if($bank){
                $bg  = 'bg-info'; $desc = 'Not Status';
                if($bank->Cair){
                    $bg = 'bg-success'; $desc = 'Sudah Cair';
                }
                elseif($bank->is_proses == 0){
                    $bg = 'bg-danger'; $desc = 'Belum Diproses Bank';
                }
                elseif($bank->is_proses == 1){
                    $bg = 'bg-warning'; $desc = 'Belum Cair';
                }
                elseif($bank->is_proses == 2){
                    $bg = 'bg-danger'; $desc = 'Ditolak';
                }
                elseif($bank->is_proses == 3){
                    $bg = 'bg-danger'; $desc = 'Sudah Cair';
                }
            }else{
                $bg  = 'bg-danger'; $desc = 'Belum Diproses.';
            }
            $this->status_cair = [
                'bg'  => $bg,
                'desc'  => $desc,
                'id'  => $id_sp2d,
                'bank'  => $bank,
            ];
            return $this->status_cair;

        }
    }
}
