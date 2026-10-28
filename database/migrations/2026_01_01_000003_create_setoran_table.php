<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setoran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pengepul_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('perkiraan_liter', 8, 2)->unsigned();
            $table->decimal('liter_aktual', 8, 2)->unsigned()->nullable();
            $table->text('alamat_jemput');
            $table->text('catatan')->nullable();
            $table->decimal('harga_per_liter_saat_itu', 10, 2)->unsigned()->nullable();
            $table->decimal('total_saldo', 15, 2)->unsigned()->nullable();
            $table->enum('status', ['menunggu', 'diproses', 'selesai', 'dibatalkan'])->default('menunggu');
            $table->timestamp('tanggal_ajukan')->useCurrent();
            $table->timestamp('tanggal_selesai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setoran');
    }
};
