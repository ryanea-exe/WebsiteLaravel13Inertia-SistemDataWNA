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
        // 1. Randomize existing NULL kabupaten in wna table
        $options = ['Ponorogo', 'Pacitan', 'Trenggalek'];
        $nullWnaIds = DB::table('wna')->whereNull('kabupaten')->pluck('id');
        foreach ($nullWnaIds as $id) {
            DB::table('wna')->where('id', $id)->update([
                'kabupaten' => $options[array_rand($options)],
            ]);
        }

        // 2. Change wna.kabupaten to NOT NULL
        Schema::table('wna', function (Blueprint $table) {
            $table->enum('kabupaten', ['Ponorogo', 'Pacitan', 'Trenggalek'])->nullable(false)->change();
        });

        // 3. Ensure any null bulan in laporan_izin_tinggal has a unique date before setting NOT NULL
        $nullLaporanIds = DB::table('laporan_izin_tinggal')->whereNull('bulan')->pluck('id');
        if ($nullLaporanIds->isNotEmpty()) {
            $usedMonths = DB::table('laporan_izin_tinggal')->whereNotNull('bulan')->pluck('bulan')->toArray();
            $year = 2026;
            $month = 1;
            foreach ($nullLaporanIds as $id) {
                while (in_array(sprintf('%04d-%02d-01', $year, $month), $usedMonths)) {
                    $month++;
                    if ($month > 12) {
                        $month = 1;
                        $year++;
                    }
                }
                $assigned = sprintf('%04d-%02d-01', $year, $month);
                $usedMonths[] = $assigned;
                DB::table('laporan_izin_tinggal')->where('id', $id)->update(['bulan' => $assigned]);
            }
        }

        // 4. Change laporan_izin_tinggal.bulan to NOT NULL
        Schema::table('laporan_izin_tinggal', function (Blueprint $table) {
            $table->date('bulan')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wna', function (Blueprint $table) {
            $table->enum('kabupaten', ['Ponorogo', 'Pacitan', 'Trenggalek'])->nullable()->change();
        });

        Schema::table('laporan_izin_tinggal', function (Blueprint $table) {
            $table->date('bulan')->nullable()->change();
        });
    }
};
