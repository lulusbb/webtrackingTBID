<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <- import

class Klien extends Model
{
    use HasFactory, SoftDeletes; // <- aktifkan soft delete

    protected $fillable = [
        'nama','lokasi_lahan','luas_lahan','luas_bangunan','kebutuhan_ruang','sertifikat',
        'arah_mata_angin','batas_keliling','foto_eksisting','konsep_bangunan','referensi',
        'budget','share_lokasi','biaya_survei','hoby','aktivitas','prioritas_ruang','kendaraan',
        'estimasi_start','target_user_kos','fasilitas_kos','layout','desain_3d','rab_boq',
        'gambar_kerja','tanggal_masuk','email','alamat_tinggal','no_hp','kode_proyek','kelas','status',             
        'keterangan','lembar_diskusi',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    
    public function surveyRequests() { return $this->hasMany(\App\Models\SurveyRequest::class); }
    public function survei()         { return $this->hasOne(\App\Models\KlienSurvei::class); }

    public function cancels()
    {
        return $this->hasMany(\App\Models\KlienCancel::class, 'klien_id');
    }

}

