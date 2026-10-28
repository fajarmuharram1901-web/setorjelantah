<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setoran', function (Blueprint $table) {
            // Kelas kualitas minyak: bersih / sedang / kurang / jelantah.
            // Default 'sedang' (pengali harga 1.0) menjaga perhitungan lama tetap sama.
            $table->string('kualitas', 20)->default('sedang')->after('perkiraan_liter')->index();
        });
    }

    public function down(): void
    {
        Schema::table('setoran', function (Blueprint $table) {
            $table->dropColumn('kualitas');
        });
    }
};
