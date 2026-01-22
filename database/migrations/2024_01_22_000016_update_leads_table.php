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
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'id')) {
                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('name')->nullable();
                $table->string('company')->nullable();
                $table->string('email')->nullable();
                $table->string('mob')->nullable();
                $table->string('gstno')->nullable();
                $table->string('location')->nullable();
                $table->string('purpose')->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->string('poc')->nullable(); // Point of Contact
                $table->string('status')->default('new'); // new, qualified, negotiating, won, lost
                $table->string('whatsapp')->nullable();
                $table->string('position')->nullable();
                $table->string('industry')->nullable();
                $table->string('website')->nullable();
                $table->decimal('values', 12, 2)->nullable();
                $table->string('language')->nullable();
                $table->json('tags')->nullable();
                
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }
            if (Schema::hasColumn('leads', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            // Drop other columns
            $columns = ['name', 'company', 'email', 'mob', 'gstno', 'location', 'purpose', 
                       'assigned_to', 'poc', 'status', 'whatsapp', 'position', 'industry', 
                       'website', 'values', 'language', 'tags'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('leads', $column)) {
                    if ($column === 'assigned_to') {
                        $table->dropForeign(['assigned_to']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
