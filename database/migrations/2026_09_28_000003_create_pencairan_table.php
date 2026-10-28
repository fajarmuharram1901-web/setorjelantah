<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pencairan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('jumlah', 15, 2);

            // ewallet | transfer | tunai
            $table->string('metode', 20)->default('ewallet');

            // Nomor HP / rekening / nama penerima sesuai metode.
            $table->string('tujuan', 120);
            $table->string('nama_penerima', 100)->nullable();

            // menunggu | disetujui | ditolak
            $table->string('status', 20)->default('menunggu')->index();

            $table->text('alasan_tolak')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diproses_pada')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pencairan');
    }
};
