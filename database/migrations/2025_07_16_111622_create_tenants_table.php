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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cnpj')->nullable();
            $table->string('social_reason')->nullable();
            $table->string('fantasy_name')->nullable();
            $table->string('address')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep')->nullable();
            $table->string('telephone')->nullable();
            $table->date('contract_start')->nullable();
            $table->boolean('active_tenant')->default(true);
            $table->foreignId('plain_id')->nullable()->constrained('plains')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
