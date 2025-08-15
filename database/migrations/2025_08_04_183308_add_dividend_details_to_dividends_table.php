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
        Schema::table('dividends', function (Blueprint $table) {
            $table->date('record_date')->nullable()->after('asset_id'); // Data Com
            $table->date('ex_date')->nullable()->after('record_date');     // Data Ex
            $table->enum('type', ['DIV', 'JSCP', 'REN'])->default('DIV')->after('ex_date'); // Tipo (Dividendo, Juros S/ Capital Próprio, Rendimentos)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dividends', function (Blueprint $table) {
            $table->dropColumn(['record_date', 'ex_date', 'type']);
        });
    }
};
