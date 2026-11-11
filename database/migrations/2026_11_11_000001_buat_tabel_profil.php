<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('jabatan');
            $table->string('ringkasan');
            $table->text('biografi')->nullable();
            $table->string('url_foto')->nullable();
            $table->string('status_ketersediaan')->default('Available');
            $table->string('email')->nullable();
            $table->string('telepon')->nullable();
            $table->string('url_linkedin')->nullable();
            $table->string('url_github')->nullable();
            $table->string('url_instagram')->nullable();
            $table->string('url_whatsapp')->nullable();
            $table->string('lokasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil');
    }
};
