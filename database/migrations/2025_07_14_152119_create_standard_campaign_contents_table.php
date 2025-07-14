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
        Schema::create('standard_campaign_contents', function (Blueprint $table) {
            $table->id();
            $table->text('goal')->nullable();
            $table->text('video_url')->nullable();
            $table->string('image_url')->nullable();
            $table->json('actions')->nullable();
            $table->json('resources')->nullable();
            $table->json('quiz')->nullable(); 
            $table->foreignId('standard_campaign_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_campaign_contents');
    }
};
