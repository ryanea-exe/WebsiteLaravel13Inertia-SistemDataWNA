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
        Schema::create('wna', function (Blueprint $table) {
            $table->id();
            $table->string('no_register', 80)->unique();
            $table->string('no_paspor', 80)->index();
            $table->string('nama', 180)->index();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('kewarganegaraan', 100)->index();
            $table->enum('jenis_izin_tinggal', ['ITAS', 'ITAP', 'VOA'])->index();
            $table->string('izin_tinggal_ke', 50)->nullable();
            $table->date('masa_berlaku')->index();
            $table->string('penjamin', 180)->nullable();
            $table->text('alamat_penjamin')->nullable();
            $table->string('nomor_telepon', 40)->nullable();
            $table->string('foto', 2048)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wna');
    }
};
