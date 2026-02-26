<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('bidang_id')->nullable()->after('akses');
            $table->unsignedBigInteger('sub_bidang_id')->nullable()->after('bidang_id');
            $table->unsignedBigInteger('pegawai_id')->nullable()->after('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bidang_id', 'sub_bidang_id', 'pegawai_id']);
        });
    }
};
