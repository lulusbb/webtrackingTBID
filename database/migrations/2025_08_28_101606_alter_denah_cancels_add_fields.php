<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('denah_cancels', function (Blueprint $table) {
            if (!Schema::hasColumn('denah_cancels', 'denah_id')) {
                $table->unsignedBigInteger('denah_id')->nullable()->index()->after('id');
            }
            if (!Schema::hasColumn('denah_cancels', 'klien_id')) {
                $table->unsignedBigInteger('klien_id')->nullable()->index()->after('denah_id');
            }
            if (!Schema::hasColumn('denah_cancels', 'nama')) {
                $table->string('nama')->nullable()->after('klien_id');
            }
            if (!Schema::hasColumn('denah_cancels', 'alamat_tinggal')) {      // <— lowercase
                $table->string('alamat_tinggal')->nullable()->after('nama');
            }
            if (!Schema::hasColumn('denah_cancels', 'lokasi_lahan')) {
                $table->string('lokasi_lahan')->nullable()->after('alamat_tinggal');
            }
            if (!Schema::hasColumn('denah_cancels', 'alasan_cancel')) {
                $table->text('alasan_cancel')->nullable()->after('lokasi_lahan');
            }
            if (!Schema::hasColumn('denah_cancels', 'canceled_by')) {
                $table->unsignedBigInteger('canceled_by')->nullable()->after('alasan_cancel');
            }
            if (!Schema::hasColumn('denah_cancels', 'canceled_at')) {
                $table->timestamp('canceled_at')->nullable()->after('canceled_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('denah_cancels', function (Blueprint $table) {
            // drop kolom jika perlu rollback
            foreach ([
                'denah_id','klien_id','nama','alamat_tinggal','lokasi_lahan',
                'alasan_cancel','canceled_by','canceled_at'
            ] as $col) {
                if (Schema::hasColumn('denah_cancels', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
