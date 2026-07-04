<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->foreignId('author_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('editor_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('featured_media_id')
                ->nullable()
                ->constrained('media')
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();

            // Enum-backed columns stored as strings, cast in the model.
            $table->string('type');
            $table->string('status')->default('draft');
            $table->string('workflow_stage')->default('research');
            $table->string('language')->default('ur');

            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->longText('body_en')->nullable();

            // Type-specific structured data (eligibility, deadlines,
            // departments, official links, etc.) — see
            // App\Enums\ArticleType::expectedMetaKeys().
            $table->json('meta')->nullable();

            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->json('seo_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('source_url')->nullable();

            $table->boolean('is_breaking')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_ai_generated')->default(false);

            $table->unsignedBigInteger('views_count')->default(0);

            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index('type');
            $table->index('is_breaking');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
