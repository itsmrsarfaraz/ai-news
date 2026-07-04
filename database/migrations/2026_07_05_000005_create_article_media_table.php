<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('role')->default('gallery');
            $table->unsignedInteger('order')->default(0);
            $table->text('caption_override')->nullable();
            $table->timestamps();

            $table->index(['article_id', 'role', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_media');
    }
};
