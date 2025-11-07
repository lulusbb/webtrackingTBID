<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProyekSelesaii extends Model
{
    protected $table = 'proyekselesaii';
    protected $guarded = []; // kita copy mass-assignment dengan hati-hati di controller
    protected $casts = [
        'tanggal_masuk'   => 'datetime',
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'datetime',
        'moved_at'        => 'datetime',
    ];
}
