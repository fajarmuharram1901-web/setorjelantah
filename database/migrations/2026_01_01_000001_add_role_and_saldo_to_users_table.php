<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Jalankan migration ini SETELAH migration bawaan Breeze (create_users_table)
// php artisan make:migration add_role_and_saldo_to_users_table --table=users

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'pengepul', 'admin'])->default('user')->after('email');
            $table->string('no_hp', 20)->nullable()->after('role');
            $table->text('alamat')->nullable()->after('no_hp');
            $table->decimal('saldo', 15, 2)->unsigned()->default(0)->after('alamat');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'no_hp', 'alamat', 'saldo']);
        });
    }
};
