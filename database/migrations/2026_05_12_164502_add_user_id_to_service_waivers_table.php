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
        if (! Schema::hasColumn('service_waivers', 'user_id')) {
            Schema::table('service_waivers', function (Blueprint $table): void {
                $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('service_waivers', 'submission_id')) {
            Schema::table('service_waivers', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('submission_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('service_waivers', 'submission_id')) {
            return;
        }

        Schema::table('service_waivers', function (Blueprint $table): void {
            $table->foreignId('submission_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
        });
    }
};
