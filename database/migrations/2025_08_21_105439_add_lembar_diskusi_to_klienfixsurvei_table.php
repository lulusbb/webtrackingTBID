<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('klienfixsurvei', function (Blueprint $table) {
            // Tambahkan kolom lembar_diskusi untuk simpan path file
            $table->string('lembar_diskusi')->nullable()->after('foto_eksisting');
        });
    }

    public function down(): void
    {
        Schema::table('klienfixsurvei', function (Blueprint $table) {
            $table->dropColumn('lembar_diskusi');
        });
    }
};
