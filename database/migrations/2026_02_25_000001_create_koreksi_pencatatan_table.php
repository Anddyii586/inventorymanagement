<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('koreksi_pencatatan', function (Blueprint $table) {
            $table->id();
            $table->string('asset_type');
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('data')->nullable();
            $table->integer('total_jumlah')->default(0);
            $table->decimal('total_harga', 20, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('koreksi_pencatatan');
    }
};
