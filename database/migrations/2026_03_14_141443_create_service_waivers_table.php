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
        Schema::create('service_waivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->dateTime('expires_at');
            $table->dateTime('signed_at')->nullable();
            $table->string('signer_name')->nullable();
            $table->string('signer_email')->nullable();
            $table->string('signer_ip', 45)->nullable();
            $table->text('signer_user_agent')->nullable();
            $table->longText('agreement_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_waivers');
    }
};
