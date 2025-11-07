<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel target (tanpa tabel *_cancels)
     */
    private array $tables = [
        'klienfixsurvei',
        'denahs',
        'exteriors',
        'meps',
        'struktur_3ds',
        'skemas',
        'rabs',
        'mous',
        'proyekjalans',
        'proyekselesaii',
    ];

    /**
     * Definisi kolom yang ingin distandarkan.
     * Setiap item: nama_kolom => closure(Blueprint $table)
     */
    private function columnDefs(): array
    {
        return [
            'nama'             => fn (Blueprint $t) => $t->string('nama', 191)->nullable(),
            'lokasi_lahan'     => fn (Blueprint $t) => $t->string('lokasi_lahan', 191)->nullable(),
            'luas_lahan'       => fn (Blueprint $t) => $t->decimal('luas_lahan', 10, 2)->nullable(),
            'luas_bangunan'    => fn (Blueprint $t) => $t->decimal('luas_bangunan', 10, 2)->nullable(),
            'kebutuhan_ruang'  => fn (Blueprint $t) => $t->text('kebutuhan_ruang')->nullable(),
            'sertifikat'       => fn (Blueprint $t) => $t->string('sertifikat', 255)->nullable(),
            'arah_mata_angin'  => fn (Blueprint $t) => $t->string('arah_mata_angin', 50)->nullable(),
            'batas_keliling'   => fn (Blueprint $t) => $t->string('batas_keliling', 191)->nullable(),
            'foto_eksisting'   => fn (Blueprint $t) => $t->string('foto_eksisting', 255)->nullable(),
            'konsep_bangunan'  => fn (Blueprint $t) => $t->string('konsep_bangunan', 191)->nullable(),
            'referensi'        => fn (Blueprint $t) => $t->string('referensi', 255)->nullable(),

            'budget'           => fn (Blueprint $t) => $t->decimal('budget', 15, 2)->nullable(),
            'share_lokasi'     => fn (Blueprint $t) => $t->string('share_lokasi', 255)->nullable(),
            'biaya_survei'     => fn (Blueprint $t) => $t->decimal('biaya_survei', 15, 2)->nullable(),
            'hoby'             => fn (Blueprint $t) => $t->string('hoby', 191)->nullable(),
            'aktivitas'        => fn (Blueprint $t) => $t->string('aktivitas', 191)->nullable(),
            'prioritas_ruang'  => fn (Blueprint $t) => $t->string('prioritas_ruang', 191)->nullable(),
            'kendaraan'        => fn (Blueprint $t) => $t->string('kendaraan', 191)->nullable(),
            'estimasi_start'   => fn (Blueprint $t) => $t->date('estimasi_start')->nullable(),
            'target_user_kos'  => fn (Blueprint $t) => $t->string('target_user_kos', 191)->nullable(),
            'fasilitas_kos'    => fn (Blueprint $t) => $t->string('fasilitas_kos', 191)->nullable(),

            'layout'           => fn (Blueprint $t) => $t->string('layout', 255)->nullable(),
            'desain_3d'        => fn (Blueprint $t) => $t->string('desain_3d', 255)->nullable(),
            'rab_boq'          => fn (Blueprint $t) => $t->string('rab_boq', 255)->nullable(),
            'gambar_kerja'     => fn (Blueprint $t) => $t->string('gambar_kerja', 255)->nullable(),

            'tanggal_masuk'    => fn (Blueprint $t) => $t->timestamp('tanggal_masuk')->nullable(),
            'email'            => fn (Blueprint $t) => $t->string('email', 191)->nullable(),
            'alamat_tinggal'   => fn (Blueprint $t) => $t->string('alamat_tinggal', 255)->nullable(),
            'no_hp'            => fn (Blueprint $t) => $t->string('no_hp', 30)->nullable(),
            'kode_proyek'      => fn (Blueprint $t) => $t->string('kode_proyek', 50)->nullable(),
            'kelas'            => fn (Blueprint $t) => $t->string('kelas', 20)->nullable(),
            'status'           => fn (Blueprint $t) => $t->string('status', 50)->nullable(),

            'keterangan'       => fn (Blueprint $t) => $t->text('keterangan')->nullable(),
            'lembar_diskusi'   => fn (Blueprint $t) => $t->string('lembar_diskusi', 255)->nullable(),
        ];
    }

    public function up(): void
    {
        $defs = $this->columnDefs();

        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $defs) {
                foreach ($defs as $col => $adder) {
                    if (!Schema::hasColumn($tableName, $col)) {
                        $adder($table);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        $cols = array_keys($this->columnDefs());

        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $cols) {
                foreach ($cols as $col) {
                    if (Schema::hasColumn($tableName, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
