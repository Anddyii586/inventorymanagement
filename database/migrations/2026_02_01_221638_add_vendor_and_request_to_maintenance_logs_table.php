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
        Schema::table('maintenance_logs', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('user_id')->constrained('vendors')->nullOnDelete();
            $table->foreignId('maintenance_request_id')->nullable()->after('vendor_id')->constrained('maintenance_requests')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_logs', function (Blueprint $table) {
            //
        });
    }
};
