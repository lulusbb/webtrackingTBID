<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('denahs', function (Blueprint $table) {
            // tambahkan hanya jika kolomnya belum ada
            if (!Schema::hasColumn('denahs','lembar_diskusi')) $table->string('lembar_diskusi')->nullable()->after('sertifikat');
            if (!Schema::hasColumn('denahs','lembar_survei'))  $table->string('lembar_survei')->nullable()->after('rab_boq');
            if (!Schema::hasColumn('denahs','catatan_survei')) $table->text('catatan_survei')->nullable()->after('lembar_survei');

            // pastikan kolom file lain tersedia (aman diulang)
            foreach (['foto_eksisting','referensi','layout','desain_3d','rab_boq','gambar_kerja'] as $col) {
                if (!Schema::hasColumn('denahs',$col)) {
                    $table->string($col)->nullable()->after('konsep_bangunan');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('denahs', function (Blueprint $table) {
            if (Schema::hasColumn('denahs','lembar_diskusi')) $table->dropColumn('lembar_diskusi');
            if (Schema::hasColumn('denahs','lembar_survei'))  $table->dropColumn('lembar_survei');
            if (Schema::hasColumn('denahs','catatan_survei')) $table->dropColumn('catatan_survei');
        });
    }
};
