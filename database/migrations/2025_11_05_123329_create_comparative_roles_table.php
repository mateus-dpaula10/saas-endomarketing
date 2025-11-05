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
        Schema::create('comparative_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagnostic_quadrant_analysis_id')->constrained('diagnostic_quadrant_analyses')->onDelete('cascade');
            $table->string('elemento'); 
            $table->text('colaboradores')->nullable();
            $table->text('gestao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparative_roles');
    }
};
