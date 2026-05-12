<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('service_waivers', 'submission_id')) {
            return;
        }

        DB::statement(<<<'SQL'
            update service_waivers
            inner join submissions on submissions.id = service_waivers.submission_id
            set service_waivers.user_id = submissions.user_id
            where service_waivers.user_id is null
        SQL);

        $foreignKeys = DB::select(<<<'SQL'
            select CONSTRAINT_NAME
            from INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            where TABLE_SCHEMA = DATABASE()
                and TABLE_NAME = 'service_waivers'
                and COLUMN_NAME = 'submission_id'
                and REFERENCED_TABLE_NAME is not null
        SQL);

        foreach ($foreignKeys as $foreignKey) {
            $constraintName = str_replace('`', '``', $foreignKey->CONSTRAINT_NAME);

            DB::statement("alter table `service_waivers` drop foreign key `{$constraintName}`");
        }

        Schema::table('service_waivers', function (Blueprint $table): void {
            $table->dropColumn('submission_id');
        });
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
