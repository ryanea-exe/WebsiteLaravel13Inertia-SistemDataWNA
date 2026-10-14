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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama', 150)->after('id');
            $table->string('username', 80)->unique()->after('nama');
            $table->enum('role', ['admin', 'user'])->default('user')->after('password');
            $table->string('foto', 2048)->nullable()->after('role');
            $table->dateTime('terakhir_login')->nullable()->after('foto');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nama', 'username', 'role', 'foto', 'terakhir_login']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
