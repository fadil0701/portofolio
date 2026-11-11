<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artikel_id')->constrained('artikel')->cascadeOnDelete();
            $table->foreignId('tag_artikel_id')->constrained('tag_artikel')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel_tag');
    }
};
