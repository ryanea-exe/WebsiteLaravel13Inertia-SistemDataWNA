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
        Schema::create('laporan_izin_tinggal', function (Blueprint $table) {
            $table->id();
            $table->string('kategori', 120)->index();
            $table->string('layanan', 150)->index();
            $table->unsignedInteger('disetujui_l')->default(0);
            $table->unsignedInteger('disetujui_p')->default(0);
            $table->unsignedInteger('ditolak_l')->default(0);
            $table->unsignedInteger('ditolak_p')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_izin_tinggal');
    }
};
