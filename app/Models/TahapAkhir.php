<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TahapAkhir extends Model
{
    use SoftDeletes;

    protected $table = 'tahap_akhirs';

    protected $fillable = [
        'klien_id','klienfixsurvei_id',
        'nama','kode_proyek','kelas',
        'lokasi_lahan','alamat_tinggal',
        'luas_lahan','luas_bangunan','kebutuhan_ruang',
        'sertifikat','arah_mata_angin','batas_keliling',
        'foto_eksisting','konsep_bangunan','referensi',
        'budget','share_lokasi','biaya_survei',
        'hoby','aktivitas','prioritas_ruang','kendaraan',
        'estimasi_start','target_user_kos','fasilitas_kos',
        'layout','desain_3d','rab_boq','gambar_kerja',
        'tanggal_masuk','email','no_hp',
        'keterangan','lembar_diskusi','lembar_survei',
        'catatan_survei','status_tahapakhir',
    ];

    protected $casts = [
        'tanggal_masuk'=>'date',
        'created_at'=>'datetime',
        'updated_at'=>'datetime',
    ];

    public function klien(){ return $this->belongsTo(Klien::class,'klien_id'); }
    
}
