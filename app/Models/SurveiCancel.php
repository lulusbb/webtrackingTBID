<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SurveiCancel extends Model
{
    use SoftDeletes;

    protected $table = 'survei_cancel';

    protected $fillable = [
        'klien_id',
        'klienfixsurvei_id',
        'nama',
        'alamat_tinggal',
        'lokasi_lahan',
        'alasan_cancel',
        'canceled_by',
        'canceled_at',
    ];

    protected $casts = [
        'canceled_at' => 'datetime',
    ];

    public function klien()        { return $this->belongsTo(Klien::class, 'klien_id'); }
    public function fix()          { return $this->belongsTo(KlienFixSurvei::class, 'klienfixsurvei_id'); }
}
