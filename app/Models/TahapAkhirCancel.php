<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahapAkhirCancel extends Model
{
    protected $table = 'tahapakhir_cancels';

    protected $fillable = [
        'tahapakhir_id','klien_id','nama','alamat_tinggal','lokasi_lahan',
        'alasan_cancel','canceled_by','canceled_at',
    ];

    protected $casts = [ 'canceled_at'=>'datetime' ];

    public function klien(){ return $this->belongsTo(Klien::class,'klien_id'); }
}
