<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `wna` MODIFY COLUMN `jenis_izin_tinggal` ENUM('ITAS', 'ITAP', 'VOA', 'ITK') NOT NULL");
        } else {
            Schema::table('wna', function (Blueprint $table) {
                $table->string('jenis_izin_tinggal', 20)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `wna` MODIFY COLUMN `jenis_izin_tinggal` ENUM('ITAS', 'ITAP', 'VOA') NOT NULL");
        } else {
            Schema::table('wna', function (Blueprint $table) {
                $table->string('jenis_izin_tinggal', 20)->change();
            });
        }
    }
};
