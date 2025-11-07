<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rab_cancels', function (Blueprint $t) {
            $t->id();
            $t->foreignId('rab_id')->nullable()->constrained('rabs')->nullOnDelete();
            $t->foreignId('klien_id')->nullable()->constrained('kliens')->nullOnDelete();

            $t->string('nama')->nullable();
            $t->string('kode_proyek')->index();

            $t->text('alasan_cancel')->nullable();
            $t->timestamp('canceled_at')->nullable();

            $t->timestamps();
            $t->softDeletes();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rab_cancels');
    }
};

