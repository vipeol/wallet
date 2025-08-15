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
        Schema::create('portfolio_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('portfolio_id')->constrained()->onDelete('cascade');
            
            // A linha ->unique([...]) foi removida daqui
            $table->date('date');

            $table->decimal('market_value', 18, 8);
            $table->decimal('total_cotas', 18, 8);
            $table->decimal('cota_value', 18, 8);
            $table->timestamps();

            // A chave única composta é definida aqui, separadamente, no final.
            $table->unique(['user_id', 'portfolio_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_snapshots');
    }
};
