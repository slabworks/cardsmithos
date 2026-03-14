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
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('default_fixed_rate', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('company_name')->nullable();
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
