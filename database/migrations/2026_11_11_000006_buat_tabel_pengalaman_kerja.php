<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengalaman_kerja', function (Blueprint $table) {
            $table->id();
            $table->string('organisasi');
            $table->string('jabatan');
            $table->string('jenis_pekerjaan')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->text('deskripsi')->nullable();
            $table->json('tanggung_jawab')->nullable();
            $table->json('pencapaian')->nullable();
            $table->json('teknologi')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengalaman_kerja');
    }
};
