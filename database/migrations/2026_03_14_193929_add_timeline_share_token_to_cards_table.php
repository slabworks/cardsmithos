<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->string('timeline_share_token', 64)->nullable()->unique()->after('photos');
        });

        DB::table('cards')->whereNull('timeline_share_token')->orderBy('id')->each(function (object $card): void {
            DB::table('cards')->where('id', $card->id)->update([
                'timeline_share_token' => Str::random(64),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('timeline_share_token');
        });
    }
};
