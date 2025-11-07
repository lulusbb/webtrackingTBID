<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skema extends Model
{
    use SoftDeletes;

    protected $table = 'skemas';

    protected $fillable = [
        'klien_id',
        'struktur3d_id',          // kalau kolom ini ada di tabel skemas
        // 'klienfixsurvei_id',    // hapus baris ini kalau kolomnya memang tidak ada

        'nama','lokasi_lahan','luas_lahan','luas_bangunan','kebutuhan_ruang','sertifikat',
        'arah_mata_angin','batas_keliling','foto_eksisting','konsep_bangunan','referensi',
        'budget','share_lokasi','biaya_survei','hoby','aktivitas','prioritas_ruang','kendaraan',
        'estimasi_start','target_user_kos','fasilitas_kos','layout','desain_3d','rab_boq',
        'gambar_kerja','tanggal_masuk','email','alamat_tinggal','no_hp','kode_proyek','kelas','status',             
        'keterangan','lembar_diskusi',
        'catatan_survei', // <— tambahkan ini
        'lembar_survei', // <= baru
        // lain-lain
        'status_skema',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
        'luas_lahan'    => 'decimal:2',
        'luas_bangunan' => 'decimal:2',
    ];

    public function klien()
    {
        return $this->belongsTo(Klien::class);
    }
}
