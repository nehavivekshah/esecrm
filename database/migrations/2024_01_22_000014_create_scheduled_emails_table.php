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
        Schema::create('scheduled_emails', function (Blueprint $table) {
            $table->id();
            $table->string('recipient_email');
            $table->string('subject');
            $table->longText('body');
            $table->dateTime('scheduled_at');
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('related_model_id')->nullable();
            $table->string('related_model_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_emails');
    }
};
