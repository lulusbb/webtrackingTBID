<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('survei_cancel', function (Blueprint $table) {
            $table->id();

            // relasi sumber data
            $table->unsignedBigInteger('klien_id')->index();
            $table->unsignedBigInteger('klienfixsurvei_id')->nullable()->index();

            // snapshot informasi penting agar arsip tetap berdiri sendiri
            $table->string('nama')->nullable();
            $table->string('Alamat_tinggal')->nullable();
            $table->string('lokasi_lahan')->nullable();

            // metadata pembatalan
            $table->text('alasan_cancel')->nullable();
            $table->unsignedBigInteger('canceled_by')->nullable()->index();
            $table->timestamp('canceled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // (opsional) FK ringan
            // $table->foreign('klien_id')->references('id')->on('kliens')->cascadeOnDelete();
            // $table->foreign('klienfixsurvei_id')->references('id')->on('klienfixsurvei')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survei_cancel');
    }
};

