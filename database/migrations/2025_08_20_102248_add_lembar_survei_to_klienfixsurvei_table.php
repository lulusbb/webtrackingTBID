<?php

// database/migrations/2025_08_20_000001_add_lembar_survei_to_klienfixsurvei.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('klienfixsurvei', function (Blueprint $table) {
            $table->string('lembar_survei')->nullable()->after('catatan_survei');
        });
    }

    public function down(): void
    {
        Schema::table('klienfixsurvei', function (Blueprint $table) {
            $table->dropColumn('lembar_survei');
        });
    }
};

