<?php

// app/Models/Denah.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Denah extends Model
{
    use SoftDeletes;

    // Daftar kolom yang boleh di-mass-assign
    protected $fillable = [
        'klien_id',
        'nama','lokasi_lahan','luas_lahan','luas_bangunan','kebutuhan_ruang','sertifikat',
        'arah_mata_angin','batas_keliling','foto_eksisting','konsep_bangunan','referensi',
        'budget','share_lokasi','biaya_survei','hoby','aktivitas','prioritas_ruang','kendaraan',
        'estimasi_start','target_user_kos','fasilitas_kos','layout','desain_3d','rab_boq',
        'gambar_kerja','tanggal_masuk','email','alamat_tinggal','no_hp','kode_proyek','kelas',
        'keterangan',
        // meta jadwal
        'schedule_at','scheduled_by',
        'catatan_survei', // <— tambahkan ini
        'lembar_survei', // <= baru
        'lembar_diskusi',
    ];
    protected $guarded = [];  // atau definisikan $fillable lengkap

    protected $casts = [
        'schedule_at'    => 'datetime',
        'tanggal_masuk'  => 'date',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    public function klien() {
        return $this->belongsTo(Klien::class, 'klien_id');
    }
}
