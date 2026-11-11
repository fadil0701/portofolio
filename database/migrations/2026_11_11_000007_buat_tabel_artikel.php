<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('kategori');
            $table->text('ringkasan')->nullable();
            $table->longText('isi');
            $table->string('url_gambar_utama')->nullable();
            $table->unsignedInteger('waktu_baca')->default(5);
            $table->boolean('unggulan')->default(false);
            $table->string('status')->default('draft');
            $table->string('judul_seo')->nullable();
            $table->string('deskripsi_seo')->nullable();
            $table->timestamp('diterbitkan_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};
