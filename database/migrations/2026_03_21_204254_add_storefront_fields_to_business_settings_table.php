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
        Schema::table('business_settings', function (Blueprint $table) {
            $table->string('store_slug', 63)->nullable()->unique();
            $table->text('bio')->nullable();
            $table->string('instagram_handle', 30)->nullable();
            $table->string('tiktok_handle', 24)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table) {
            $table->dropColumn(['store_slug', 'bio', 'instagram_handle', 'tiktok_handle']);
        });
    }
};
