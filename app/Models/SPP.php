<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// use

class SPP extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'ta_spp';
    protected $guarded = [];

    public function skpd()
    {
        return $this->belongsTo(OPD::class,'id_skpd','id_skpd');
    }

    public function cetak()
    {
        return $this->belongsTo(SPP_CETAK::class,'id_spp','id_spp');
    }

    public function rekening()
    {
        return $this->hasMany(SPP_REKENING::class,'id_spp','id_spp');
    }

    public function ceklist()
    {
        return $this->hasMany(Ta_Ceklist::class,'id_spp','id_spp');
    }

    public function totalWeight()
    {
        $total = 0;
        foreach ($this->orderItem as $value) {
            $total += $value->weight();
        }
        return $total;
    }

    public function Cek_Ceklist(){
        $id_spp = $this->id_spp;
        $valid_bend = $this->asis_final_bend;
        if(!$valid_bend){
             $ref  = Ref_Ceklist::get();
             foreach($ref as $dat){
                $cek = Ta_Ceklist::where('ref_id',$dat->id)->where('id_spp',$id_spp)->first();
                if(!$cek){
                    Ta_Ceklist::create([
                        'ref_id'  => $dat->id,
                        'id_spp'  => $id_spp,
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
        $this->cek_list = Ta_Ceklist::where('id_spp',$id_spp)->get();
        return $this->cek_list;
    }
}
