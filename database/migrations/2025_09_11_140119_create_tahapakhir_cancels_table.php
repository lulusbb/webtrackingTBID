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
        // ========== Tahap Akhir Cancels ==========
        Schema::create('tahapakhir_cancels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tahapakhir_id')->nullable()->index();
            $table->unsignedBigInteger('klien_id')->nullable()->index();
            $table->string('nama')->nullable();
            $table->string('alamat_tinggal')->nullable();
            $table->string('lokasi_lahan')->nullable();
            $table->text('alasan_cancel')->nullable();
            $table->unsignedBigInteger('canceled_by')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahapakhir_cancels');
    }
};
