<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('gmail_drafts');
        Schema::dropIfExists('gmail_attachments');
        Schema::dropIfExists('gmail_messages');
        Schema::dropIfExists('gmail_threads');

        if (! Schema::hasTable('gmail_contacts')) {
            Schema::create('gmail_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gmail_account_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->string('email')->index();
                $table->string('name')->nullable();
                $table->text('latest_subject')->nullable();
                $table->text('latest_snippet')->nullable();
                $table->string('latest_gmail_message_id')->nullable();
                $table->string('latest_gmail_thread_id')->nullable();
                $table->timestamp('last_message_at')->nullable()->index();
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();

                $table->unique(['gmail_account_id', 'email']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gmail_contacts');
    }
};
