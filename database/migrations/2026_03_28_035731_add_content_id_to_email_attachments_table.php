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
        if (! Schema::hasColumn('email_attachments', 'content_id')) {
            Schema::table('email_attachments', function (Blueprint $table) {
                $table->string('content_id')->nullable()->after('gmail_attachment_id');
            });
        }

        // Gmail attachment IDs can exceed 255 chars; nullable for inline-data attachments
        Schema::table('email_attachments', function (Blueprint $table) {
            $table->text('gmail_attachment_id')->nullable()->change();
        });

        if (! Schema::hasColumn('email_attachments', 'inline_data')) {
            Schema::table('email_attachments', function (Blueprint $table) {
                $table->mediumText('inline_data')->nullable()->after('content_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_attachments', function (Blueprint $table) {
            $table->dropColumn('content_id');
        });
    }
};
