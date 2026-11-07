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
        $validKabupaten = ['Ponorogo', 'Pacitan', 'Trenggalek'];

        // Sanitize any existing non-conforming kabupaten values
        DB::table('permohonan')
            ->whereNotNull('kabupaten')
            ->whereNotIn('kabupaten', $validKabupaten)
            ->update(['kabupaten' => 'Ponorogo']);

        DB::table('penerbitan')
            ->whereNotNull('kabupaten')
            ->whereNotIn('kabupaten', $validKabupaten)
            ->update(['kabupaten' => 'Ponorogo']);

        Schema::table('permohonan', function (Blueprint $table): void {
            $table->enum('kabupaten', ['Ponorogo', 'Pacitan', 'Trenggalek'])->nullable()->change();
        });

        Schema::table('penerbitan', function (Blueprint $table): void {
            $table->enum('kabupaten', ['Ponorogo', 'Pacitan', 'Trenggalek'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonan', function (Blueprint $table): void {
            $table->string('kabupaten', 120)->nullable()->change();
        });

        Schema::table('penerbitan', function (Blueprint $table): void {
            $table->string('kabupaten', 120)->nullable()->change();
        });
    }
};
