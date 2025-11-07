<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MepCancel extends Model
{
    protected $table = 'mep_cancels';

    protected $fillable = [
        'mep_id','klien_id','nama','alamat_tinggal','lokasi_lahan',
        'alasan_cancel','canceled_by','canceled_at',
    ];

    protected $casts = [ 'canceled_at'=>'datetime' ];

    public function klien(){ return $this->belongsTo(Klien::class,'klien_id'); }
}
