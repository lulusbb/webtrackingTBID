<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ProyekJalan extends Model
{
    use SoftDeletes;

    /**
     * Nama tabel (pastikan sama dengan nama tabel di DB)
     */
    protected $table = 'proyekjalans';

    /**
     * Kolom yang boleh diisi mass-assignment
     * (sesuaikan dengan struktur tabel kamu)
     */
    // app/Models/ProyekJalan.php

    protected $fillable = [
        'klien_id','mou_id','rab_id',
        'nama','lokasi_lahan','luas_lahan','luas_bangunan','kebutuhan_ruang','sertifikat',
        'arah_mata_angin','batas_keliling','foto_eksisting','konsep_bangunan','referensi',
        'budget','share_lokasi','biaya_survei','hoby','aktivitas','prioritas_ruang','kendaraan',
        'estimasi_start','target_user_kos','fasilitas_kos','layout','desain_3d','rab_boq',
        'gambar_kerja',              // ← TAMBAH
        'tanggal_masuk',
        'tanggal_mulai',             // ← TAMBAH
        'email','alamat_tinggal','no_hp','kode_proyek','kelas','status',
        'keterangan','lembar_diskusi','lembar_survei',
        'status_progres',
    ];

    protected $casts = [
        'tanggal_masuk'  => 'datetime',
        'tanggal_mulai'  => 'date', // boleh 'datetime', tapi 'date' biasanya pas
        'luas_lahan'     => 'decimal:2',
        'luas_bangunan'  => 'decimal:2',
        'budget'         => 'integer',
        'status_progres' => 'integer',
    ];


    /**
     * Default value status_progres = 0 saat create (kalau belum diisi)
     */
    protected static function booted()
    {
        static::creating(function (self $m) {
            if (is_null($m->status_progres)) {
                $m->status_progres = 0;
            }
        });
    }

    /**
     * Mutator: pastikan status_progres selalu 0..100
     */
    public function setStatusProgresAttribute($value): void
    {
        $this->attributes['status_progres'] = max(0, min(100, (int) $value));
    }

    /**
     * Accessor label progress, contoh: "75%"
     */
    public function getProgressLabelAttribute(): string
    {
        $v = $this->status_progres ?? 0;
        return $v . '%';
    }

    /* ===================== RELASI ===================== */

    public function klien()
    {
        return $this->belongsTo(Klien::class);
    }

    public function mou()
    {
        return $this->belongsTo(Mou::class);
    }

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }
}
