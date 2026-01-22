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
        Schema::create('fcmregs', function (Blueprint $table) {
            $table->id();
            $table->string('regID')->unique();
            $table->string('mono')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcmregs');
    }
};
