<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KlienFixSurvei extends Model
{
    protected $table = 'klienfixsurvei';

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
        // status survei
        'status_survei','survey_done_at',
        // lampiran & catatan
        'catatan_survei','lembar_survei','lembar_diskusi',
        // referensi ke survey_requests
        'survey_request_id',
    ];

    protected $casts = [
        'schedule_at'    => 'datetime',
        'survey_done_at' => 'datetime',
        'tanggal_masuk'  => 'date',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    protected static function booted()
    {
        // Saat INSERT
        static::creating(function (self $m) {
            if (empty($m->status_survei)) {
                $m->status_survei = 'Belum diSurvei';
            }
            // pastikan tidak auto-terisi
            $m->survey_done_at = null;
        });

        // Saat INSERT & UPDATE: jaga konsistensi
        static::saving(function (self $m) {
            if (is_null($m->survey_done_at)) {
                $m->status_survei = 'Belum diSurvei';
            }
        });
    }

    public function klien()
    {
        return $this->belongsTo(Klien::class, 'klien_id');
    }
}
