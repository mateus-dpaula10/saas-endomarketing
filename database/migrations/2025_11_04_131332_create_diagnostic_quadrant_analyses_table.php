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
        Schema::create('diagnostic_quadrant_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade'); 
            $table->foreignId('diagnostic_id')->constrained()->onDelete('cascade');
            $table->string('role'); 
            $table->json('medias');
            $table->json('classificacao');
            $table->json('sinais');
            $table->text('resumo')->nullable();
            $table->text('resumo_geral')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnostic_quadrant_analyses');
    }
};
