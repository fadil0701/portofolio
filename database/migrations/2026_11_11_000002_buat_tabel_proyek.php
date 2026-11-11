<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyek', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('kategori');
            $table->string('ringkasan');
            $table->text('pernyataan_masalah')->nullable();
            $table->text('ringkasan_solusi')->nullable();
            $table->json('tumpukan_teknologi')->nullable();
            $table->json('fitur_utama')->nullable();
            $table->text('ringkasan_arsitektur')->nullable();
            $table->string('url_gambar_pratinjau')->nullable();
            $table->boolean('unggulan')->default(false);
            $table->string('status')->default('published');
            $table->string('url_demo')->nullable();
            $table->string('url_github')->nullable();
            $table->text('hasil_proyek')->nullable();
            $table->timestamp('diterbitkan_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyek');
    }
};
