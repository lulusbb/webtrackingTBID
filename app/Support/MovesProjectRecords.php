<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait MovesProjectRecords
{
    /** Kolom-kolom wajib yang diminta */
    private array $REQUIRED_COLS = [
        'nama','lokasi_lahan','luas_lahan','luas_bangunan','kebutuhan_ruang','sertifikat',
        'arah_mata_angin','batas_keliling','foto_eksisting','konsep_bangunan','referensi',
        'budget','share_lokasi','biaya_survei','hoby','aktivitas','prioritas_ruang','kendaraan',
        'estimasi_start','target_user_kos','fasilitas_kos','layout','desain_3d','rab_boq',
        'gambar_kerja','tanggal_masuk','email','alamat_tinggal','no_hp','kode_proyek','kelas','status',
        'keterangan','lembar_diskusi',
    ];

    /** Helper “ambil yang pertama tak-kosong” */
    private function pick(...$vals)
    {
        foreach ($vals as $v) if (!blank($v)) return $v;
        return null;
    }

    /**
     * Bangun payload standar untuk tabel tujuan.
     * - Memakai fallback dari relasi $model->klien bila ada.
     * - Hanya menyertakan kolom yang memang ada di tabel tujuan.
     */
    private function buildMovePayload(Model $model, string $toTable, array $extra = []): array
    {
        $k = $model->klien ?? null;

        $all = [
            'klien_id'          => $this->pick($model->klien_id, optional($k)->id),
            'klienfixsurvei_id' => $model->klienfixsurvei_id ?? null,

            'nama'              => $this->pick($model->nama, optional($k)->nama) ?? 'Tanpa Nama',
            'email'             => $this->pick($model->email, optional($k)->email),
            'no_hp'             => $this->pick($model->no_hp, optional($k)->no_hp),
            'alamat_tinggal'    => $this->pick($model->alamat_tinggal, optional($k)->alamat_tinggal),

            'kode_proyek'       => $this->pick($model->kode_proyek, optional($k)->kode_proyek) ?? '-',
            'kelas'             => $this->pick($model->kelas, optional($k)->kelas),

            'lokasi_lahan'      => $this->pick($model->lokasi_lahan, optional($k)->lokasi_lahan),
            'luas_lahan'        => $model->luas_lahan,
            'luas_bangunan'     => $model->luas_bangunan,
            'kebutuhan_ruang'   => $model->kebutuhan_ruang,

            'sertifikat'        => $model->sertifikat,
            'arah_mata_angin'   => $model->arah_mata_angin,
            'batas_keliling'    => $model->batas_keliling,
            'foto_eksisting'    => $model->foto_eksisting,

            'konsep_bangunan'   => $model->konsep_bangunan,
            'referensi'         => $model->referensi,
            'budget'            => $model->budget,
            'share_lokasi'      => $model->share_lokasi,
            'biaya_survei'      => $model->biaya_survei,

            'hoby'              => $model->hoby,
            'aktivitas'         => $model->aktivitas,
            'prioritas_ruang'   => $model->prioritas_ruang,
            'kendaraan'         => $model->kendaraan,
            'estimasi_start'    => $model->estimasi_start,
            'target_user_kos'   => $model->target_user_kos,
            'fasilitas_kos'     => $model->fasilitas_kos,

            'layout'            => $model->layout,
            'desain_3d'         => $model->desain_3d,
            'rab_boq'           => $model->rab_boq,
            'gambar_kerja'      => $model->gambar_kerja,

            // tanggal_masuk fallback ke created_at model
            'tanggal_masuk'     => $this->pick($model->tanggal_masuk, $model->created_at),
            'keterangan'        => $model->keterangan ?? null,
            'lembar_diskusi'    => $model->lembar_diskusi,
            'lembar_survei'     => $model->lembar_survei ?? null,

            // bila sumber punya kolom 'status'
            'status'            => $model->status ?? null,

            'created_at'        => now(),
            'updated_at'        => now(),
        ];

        // Ambil kolom yang benar2 ada di tabel tujuan
        $cols    = Schema::getColumnListing($toTable);
        $payload = array_intersect_key(($all + $extra), array_flip($cols));

        // Default minimal
        if (in_array('nama', $cols) && empty($payload['nama']))        $payload['nama'] = 'Tanpa Nama';
        if (in_array('kode_proyek', $cols) && empty($payload['kode_proyek'])) $payload['kode_proyek'] = '-';

        return $payload;
    }

    /**
     * Validasi: pastikan tabel tujuan memiliki semua kolom REQUIRED_COLS.
     * (Kalau kamu sudah menjalankan migration penambahan kolom, ini akan lolos.)
     */
    private function assertRequiredColumnsExist(string $toTable): void
    {
        $existing = Schema::getColumnListing($toTable);
        $missing  = array_values(array_diff($this->REQUIRED_COLS, $existing));

        if (!empty($missing)) {
            abort(500, "Kolom wajib belum lengkap di tabel {$toTable}: ".implode(', ', $missing).". Jalankan migration update kolom terlebih dahulu.");
        }
    }

    /**
     * Mesin umum pemindah data antar tabel.
     * - $extra: field tambahan spesifik tabel tujuan (akan di-intersect).
     * - $after: callback opsional setelah insert (mis: update status klien).
     */
    private function moveModelToTable(Model $model, string $toTable, array $extra = [], ?callable $after = null)
    {
        return DB::transaction(function () use ($model, $toTable, $extra, $after) {
            // Pastikan kolom wajib sudah ada (mengandalkan migration)
            $this->assertRequiredColumnsExist($toTable);

            // Siapkan payload & insert
            $payload = $this->buildMovePayload($model, $toTable, $extra);
            DB::table($toTable)->insert($payload);

            // Hapus sumber (soft/hard aman)
            try { $model->forceDelete(); } catch (\Throwable $e) { $model->delete(); }

            // After hook
            if ($after) $after($model, $payload);

            return true;
        });
    }
}
