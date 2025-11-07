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
        Schema::create('role_messages', function (Blueprint $table) {
            $table->id();

            // Room chat berbasis pasangan role, mis: "admin-marketing"
            $table->string('room', 64)->index();

            // Pengirim
            $table->unsignedBigInteger('sender_id')->index();     // id user pengirim
            $table->string('sender_role', 20)->index();           // role pengirim
            $table->string('sender_name', 100)->nullable();       // untuk ditampilkan cepat

            // Penerima (berdasarkan role)
            $table->string('recipient_role', 20)->index();

            // Isi pesan
            $table->text('body');

            // Ditandai telah dibaca (oleh role penerima)
            $table->timestamp('seen_at')->nullable()->index();

            $table->timestamps();

            // Index tambahan untuk load chat per-room urut waktu
            $table->index(['room', 'created_at']);
        });

        // Catatan:
        // Jika ingin pakai FK ke users:
        // Schema::table('role_messages', function (Blueprint $table) {
        //     $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_messages');
    }
};
