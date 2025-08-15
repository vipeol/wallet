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
        Schema::table('portfolio_snapshots', function (Blueprint $table) {
            $table->decimal('total_acquisition_cost', 18, 8)->default(0)->after('market_value');
            $table->decimal('unrealized_profit_loss', 18, 8)->default(0)->after('total_acquisition_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portfolio_snapshots', function (Blueprint $table) {
            $table->dropColumn(['total_acquisition_cost', 'unrealized_profit_loss']);
        });
    }
};
