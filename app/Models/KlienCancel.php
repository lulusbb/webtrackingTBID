<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KlienCancel extends Model
{
    protected $table = 'klien_cancels';
    protected $guarded = [];          // <— penting supaya payload tidak dibuang
    protected $casts = [
        'tanggal_masuk' => 'date',
        'canceled_at'   => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function klien()
    {
        return $this->belongsTo(Klien::class, 'klien_id');
    }
}
