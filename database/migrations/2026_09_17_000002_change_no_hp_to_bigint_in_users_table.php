<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bersihkan no_hp non-angka sebelum diubah ke bigInteger.
        // REGEXP hanya ada di MySQL; SQLite memakai GLOB.
        $berisiNonAngka = match (DB::getDriverName()) {
            'sqlite' => "no_hp GLOB '*[^0-9]*'",
            default => "no_hp NOT REGEXP '^[0-9]+$'",
        };

        DB::statement("UPDATE users SET no_hp = NULL WHERE no_hp IS NOT NULL AND {$berisiNonAngka}");

        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('no_hp')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('no_hp', 20)->nullable()->change();
        });
    }
};
