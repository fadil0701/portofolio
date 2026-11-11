<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_situs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_situs');
            $table->string('slogan')->nullable();
            $table->string('email_utama')->nullable();
            $table->string('url_linkedin')->nullable();
            $table->string('url_github')->nullable();
            $table->string('url_instagram')->nullable();
            $table->string('url_whatsapp')->nullable();
            $table->string('url_logo')->nullable();
            $table->string('url_favicon')->nullable();
            $table->boolean('mode_pemeliharaan')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_situs');
    }
};
