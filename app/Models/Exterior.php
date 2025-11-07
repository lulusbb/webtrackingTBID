<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Exterior extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'exteriors';

    // Lebih fleksibel daripada fillable panjang — hindari mass-assignment error
    protected $guarded = [];

    // Cast tanggal (dipakai untuk format di DataTables)
    protected $casts = [
        'tanggal_masuk' => 'datetime',   // bisa 'date' juga, tapi datetime aman
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    /* ================= Relasi ================ */
    public function klien()
    {
        return $this->belongsTo(Klien::class, 'klien_id');
    }

    public function klienFixSurvei()
    {
        return $this->belongsTo(KlienFixSurvei::class, 'klienfixsurvei_id');
    }

    // (opsional) kalau nantinya lanjut ke MEP
    public function mep()
    {
        return $this->hasOne(Mep::class, 'exterior_id');
    }
}
