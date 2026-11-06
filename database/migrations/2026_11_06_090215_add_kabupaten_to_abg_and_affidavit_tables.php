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
        Schema::table('abg', function (Blueprint $table) {
            $table->enum('kabupaten', ['Ponorogo', 'Pacitan', 'Trenggalek'])->nullable()->after('kewarganegaraan')->index();
        });

        Schema::table('affidavit', function (Blueprint $table) {
            $table->enum('kabupaten', ['Ponorogo', 'Pacitan', 'Trenggalek'])->nullable()->after('kewarganegaraan')->index();
        });

        // Populate existing records with valid kabupaten
        $options = ['Ponorogo', 'Pacitan', 'Trenggalek'];
        $abgIds = DB::table('abg')->whereNull('kabupaten')->pluck('id');
        foreach ($abgIds as $id) {
            DB::table('abg')->where('id', $id)->update([
                'kabupaten' => $options[array_rand($options)],
            ]);
        }

        $affidavitIds = DB::table('affidavit')->whereNull('kabupaten')->pluck('id');
        foreach ($affidavitIds as $id) {
            DB::table('affidavit')->where('id', $id)->update([
                'kabupaten' => $options[array_rand($options)],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('abg', function (Blueprint $table) {
            $table->dropIndex(['kabupaten']);
            $table->dropColumn('kabupaten');
        });

        Schema::table('affidavit', function (Blueprint $table) {
            $table->dropIndex(['kabupaten']);
            $table->dropColumn('kabupaten');
        });
    }
};
