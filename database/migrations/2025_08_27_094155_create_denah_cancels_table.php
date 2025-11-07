<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('denah_cancels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('denah_id')->nullable()->index();
            $table->unsignedBigInteger('klien_id')->nullable()->index();
            $table->string('nama')->nullable();
            $table->string('Alamat_tinggal')->nullable();
            $table->string('lokasi_lahan')->nullable();
            $table->text('alasan_cancel')->nullable();
            $table->unsignedBigInteger('canceled_by')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('denah_cancels');
    }
};
