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
        Schema::table('assets', function (Blueprint $table) {
            // Percentual que o usuário deseja ter deste ativo na carteira.
            // 5, 2 permite valores como 10.50%
            $table->decimal('target_percentage', 5, 2)->nullable()->default(0)->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('target_percentage');
        });
    }
};
