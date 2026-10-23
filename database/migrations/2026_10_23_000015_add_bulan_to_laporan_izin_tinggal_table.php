<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * CR-008: Add nullable `bulan` column to `laporan_izin_tinggal` with unique constraint.
     * In MySQL and SQLite, unique constraint allows multiple NULL values for legacy records.
     */
    public function up(): void
    {
        Schema::table('laporan_izin_tinggal', function (Blueprint $table) {
            $table->date('bulan')->nullable()->after('id')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_izin_tinggal', function (Blueprint $table) {
            $table->dropUnique(['bulan']);
            $table->dropColumn('bulan');
        });
    }
};
