<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::table('skemas', function (Blueprint $table) {
        $table->string('lembar_survei')->nullable()->after('rab_boq');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skemas', function (Blueprint $table) {
            //
        });
    }
};
