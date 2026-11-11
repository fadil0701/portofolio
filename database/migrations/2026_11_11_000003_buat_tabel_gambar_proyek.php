<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gambar_proyek', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyek_id')->constrained('proyek')->cascadeOnDelete();
            $table->string('url_gambar');
            $table->string('keterangan')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gambar_proyek');
    }
};
