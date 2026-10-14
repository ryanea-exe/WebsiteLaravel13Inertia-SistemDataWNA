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
        Schema::create('permohonan', function (Blueprint $table) {
            $table->id();
            $table->string('no_paspor', 80)->nullable()->index();
            $table->string('nama', 180)->index();
            $table->string('kewarganegaraan', 100);
            $table->string('layanan', 150)->index();
            $table->date('tanggal')->index();
            $table->string('kabupaten', 120)->nullable()->index();
            $table->text('permohonan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonan');
    }
};
