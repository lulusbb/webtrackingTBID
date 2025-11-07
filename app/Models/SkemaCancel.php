<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SkemaCancel extends Model
{
    use SoftDeletes;

    protected $table = 'skema_cancels';

    protected $fillable = [
        'skema_id',
        'klien_id',
        'nama',
        'kode_proyek',
        'lokasi_lahan',   // ✅ kolom baru
        'alasan_cancel',
        'canceled_at',
    ];

    protected $casts = [
        'canceled_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];

    // RELATIONS
    public function skema()
    {
        return $this->belongsTo(Skema::class);
    }

    public function klien()
    {
        return $this->belongsTo(Klien::class);
    }
}
