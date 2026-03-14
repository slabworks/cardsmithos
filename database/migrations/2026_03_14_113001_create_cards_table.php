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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name')->index();
            $table->longText('work_done')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('condition_before')->nullable()->index();
            $table->string('condition_after')->nullable()->index();
            $table->decimal('restoration_hours', 5, 2)->nullable();
            $table->decimal('estimated_fee', 10, 2)->nullable();
            $table->json('photos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
