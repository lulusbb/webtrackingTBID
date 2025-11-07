<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('skema_cancels', function (Blueprint $t) {
            $t->id();
            $t->foreignId('skema_id')->nullable()->constrained('skemas')->nullOnDelete();
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
        Schema::dropIfExists('skema_cancels');
    }
};

