<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keahlian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_keahlian_id')->constrained('kategori_keahlian')->cascadeOnDelete();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('tingkat')->nullable();
            $table->string('ikon')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keahlian');
    }
};
