<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_statistics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('source')->default('custom')->index();
            $table->string('system_key')->nullable()->index();
            $table->string('category')->index();
            $table->string('group_name')->nullable()->index();
            $table->string('period')->index();
            $table->string('value_type')->default('number');
            $table->string('input_method')->default('manual')->index();
            $table->text('description')->nullable();
            $table->json('config')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
        });

        Schema::create('business_statistic_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('business_statistic_id')->constrained()->cascadeOnDelete();
            $table->date('period_start')->nullable()->index();
            $table->date('period_end')->nullable()->index();
            $table->dateTime('recorded_at')->index();
            $table->decimal('value', 12, 2);
            $table->string('source')->default('manual')->index();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_statistic_records');
        Schema::dropIfExists('business_statistics');
    }
};
