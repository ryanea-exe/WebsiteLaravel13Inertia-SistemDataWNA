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
        Schema::create('affidavit', function (Blueprint $table) {
            $table->id();
            $table->string('no_register', 80)->unique();
            $table->string('no_paspor', 80)->index();
            $table->string('nama', 180)->index();
            $table->string('tempat_lahir', 120)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('kewarganegaraan', 100)->index();
            $table->date('masa_berlaku')->nullable()->index();
            $table->string('nama_ayah', 180)->nullable();
            $table->string('kewarganegaraan_ayah', 100)->nullable();
            $table->string('nama_ibu', 180)->nullable();
            $table->string('kewarganegaraan_ibu', 100)->nullable();
            $table->string('nomor_telepon', 40)->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto', 2048)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affidavit');
    }
};
