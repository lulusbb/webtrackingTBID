<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rabs', function (Blueprint $table) {
            // letakkan setelah rab_boq agar rapi dengan urutan berkas lainnya
            if (!Schema::hasColumn('rabs', 'lembar_survei')) {
                $table->string('lembar_survei')->nullable()->after('rab_boq');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rabs', function (Blueprint $table) {
            if (Schema::hasColumn('rabs', 'lembar_survei')) {
                $table->dropColumn('lembar_survei');
            }
        });
    }
};

