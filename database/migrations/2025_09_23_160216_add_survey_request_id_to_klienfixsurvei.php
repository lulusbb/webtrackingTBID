<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('klienfixsurvei', function (Blueprint $table) {
            if (!Schema::hasColumn('klienfixsurvei', 'survey_request_id')) {
                $table->unsignedBigInteger('survey_request_id')
                      ->nullable()
                      ->after('klien_id');
                $table->index('survey_request_id', 'kfs_srid_idx');
                // kalau mau FK (opsional):
                // $table->foreign('survey_request_id')->references('id')->on('survey_requests')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('klienfixsurvei', function (Blueprint $table) {
            if (Schema::hasColumn('klienfixsurvei', 'survey_request_id')) {
                // $table->dropForeign(['survey_request_id']); // kalau tadi bikin FK
                $table->dropIndex('kfs_srid_idx');
                $table->dropColumn('survey_request_id');
            }
        });
    }
};
