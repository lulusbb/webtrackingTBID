<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Struktur3dCancel extends Model
{
    use SoftDeletes;

    protected $table = 'struktur_3d_cancels';

    protected $fillable = [
        'struktur3d_id','klien_id',
        'nama','kode_proyek','lokasi_lahan',
        'alasan_cancel','canceled_at',
    ];

    protected $casts = [
        'canceled_at' => 'datetime',
    ];

    // RELATIONS
    public function struktur3d() { return $this->belongsTo(Struktur3d::class, 'struktur3d_id'); }
    public function klien()      { return $this->belongsTo(Klien::class); }
}
