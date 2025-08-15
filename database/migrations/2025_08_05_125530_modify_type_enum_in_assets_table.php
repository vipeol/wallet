<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            DB::statement("ALTER TABLE assets MODIFY COLUMN type ENUM('stock', 'fii', 'crypto', 'fixed_income', 'currency')");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            DB::statement("ALTER TABLE assets MODIFY COLUMN type ENUM('stock', 'fii', 'crypto', 'fixed_income')");
        });
    }
};
