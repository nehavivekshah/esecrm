<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_departments', function (Blueprint $table) {
            $table->id();
            $table->integer('client_id'); // Using integer to match likely Clients id type, or use unsignedBigInteger if Clients uses bigIncrements
            $table->string('name');
            $table->string('gst_no')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_departments');
    }
};
