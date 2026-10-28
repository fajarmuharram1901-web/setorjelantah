<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // setoran | pencairan | bonus | penyesuaian
            $table->string('tipe', 30)->index();

            // Bertanda plus = saldo masuk, minus = saldo keluar.
            $table->decimal('jumlah', 15, 2);

            $table->decimal('saldo_sebelum', 15, 2)->default(0);
            $table->decimal('saldo_sesudah', 15, 2)->default(0);

            // Tabel yang dirujuk tanpa foreign key agar tidak mengunci skema.
            $table->string('referensi_tipe', 30)->nullable();
            $table->unsignedBigInteger('referensi_id')->nullable();
            $table->index(['referensi_tipe', 'referensi_id']);

            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
