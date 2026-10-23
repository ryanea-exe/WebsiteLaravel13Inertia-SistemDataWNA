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
        Schema::table('abg', function (Blueprint $table) {
            $table->dropIndex(['no_paspor']);
            $table->dropColumn('no_paspor');
        });
    }

    /**
     * Reverse the migrations.
     * Note: Schema rollback restores the column structure as nullable,
     * but historical data values cannot be recovered as approved for permanent removal.
     */
    public function down(): void
    {
        Schema::table('abg', function (Blueprint $table) {
            $table->string('no_paspor', 80)->nullable()->after('no_register')->index();
        });
    }
};
