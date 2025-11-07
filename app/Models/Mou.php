<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mou extends Model
{
    use SoftDeletes;

    protected $table = 'mous';

    protected $fillable = [
        'klien_id','tahap_akhir_id',
        'nama','kode_proyek','kelas',
        'alamat_tinggal','lokasi_lahan',
        'luas_lahan','luas_bangunan',
        'kebutuhan_ruang','arah_mata_angin','batas_keliling','konsep_bangunan',
        'referensi',
        'budget','lembar_diskusi','layout','desain_3d','rab_boq','gambar_kerja',
        'lembar_survei',        // ← PENTING: tambahkan ini
        'catatan_survei',
        'keterangan','tanggal_masuk',
        'status_mou',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
        'luas_lahan'    => 'decimal:2',
        'luas_bangunan' => 'decimal:2',
    ];

    // RELATIONS
    public function klien() { return $this->belongsTo(Klien::class); }
    public function rab()   { return $this->belongsTo(Rab::class); }

    public function tahapAkhir()
    {
        return $this->belongsTo(\App\Models\TahapAkhir::class, 'tahap_akhir_id');
    }
}

