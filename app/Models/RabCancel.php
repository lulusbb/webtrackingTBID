<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RabCancel extends Model
{
    use SoftDeletes; // ➜ pastikan tabel punya kolom deleted_at. Jika tidak, hapus trait ini.

    protected $table = 'rab_cancels';

    // Jika tabel tidak punya created_at & updated_at:
    public $timestamps = false;

    protected $fillable = [
        'rab_id','klien_id',
        'nama','kode_proyek','lokasi_lahan',
        'alasan_cancel','canceled_at',
    ];

    protected $casts = [
        'canceled_at' => 'datetime',
    ];

    // RELATIONS
    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    public function klien()
    {
        return $this->belongsTo(Klien::class);
    }
}
