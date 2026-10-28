<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pengaturan sederhana (kunci -> nilai) untuk opsi yang bisa diubah
 * admin lewat antarmuka, misalnya status kunci harga.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('kunci')->unique();
            $table->text('nilai')->nullable();
            $table->timestamps();
        });

        // Bawaan: aturan jeda 7 hari aktif, sesuai permintaan.
        Schema::getConnection()->table('pengaturan')->insert([
            'kunci' => 'harga_terkunci',
            'nilai' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
