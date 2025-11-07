<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Struktur3d extends Model
{
    use SoftDeletes;

    protected $table = 'struktur_3ds';

protected $fillable = [
    'klien_id','klienfixsurvei_id',
        'nama','lokasi_lahan','luas_lahan','luas_bangunan','kebutuhan_ruang','sertifikat',
        'arah_mata_angin','batas_keliling','foto_eksisting','konsep_bangunan','referensi',
        'budget','share_lokasi','biaya_survei','hoby','aktivitas','prioritas_ruang','kendaraan',
        'estimasi_start','target_user_kos','fasilitas_kos','layout','desain_3d','rab_boq',
        'gambar_kerja','tanggal_masuk','email','alamat_tinggal','no_hp','kode_proyek','kelas','status',             
        'keterangan','lembar_diskusi',

        // ➕ kolom baru
        'catatan_survei',
        // <-- tambahkan ini
        'status_denah',
];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
        'luas_lahan'    => 'decimal:2',
        'luas_bangunan' => 'decimal:2',
        // opsional: agar budget tidak dibulatkan ke float
        'budget'        => 'integer',
    ];

    protected $attributes = [
        'status_denah' => 'draft',
    ];

    /* ===================== RELATIONS ===================== */

    public function klien()
    {
        return $this->belongsTo(Klien::class);
    }

    public function klienFixSurvei()
    {
        return $this->belongsTo(KlienFixSurvei::class, 'klienfixsurvei_id');
    }

    public function skemas()
    {
        return $this->hasMany(Skema::class, 'struktur3d_id');
    }
}
