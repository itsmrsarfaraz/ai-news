<?php

namespace App\Actions;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Str;

/**
 * Generates a unique, URL-safe slug for a given table/column, appending
 * an incrementing suffix ("-1", "-2", ...) on collision.
 *
 * Shared by CategoryController and ArticleController so slug logic is
 * defined once rather than duplicated per resource.
 */
class GenerateUniqueSlugAction
{
    public function __construct(
        private readonly ConnectionInterface $db,
    ) {}

    public function handle(string $source, string $table, ?int $ignoreId = null, string $column = 'slug'): string
    {
        $base = Str::slug($source);
        $base = $base !== '' ? $base : Str::random(8);

        $slug = $base;
        $suffix = 1;

        while ($this->slugExists($table, $column, $slug, $ignoreId)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $table, string $column, string $slug, ?int $ignoreId): bool
    {
        return $this->db->table($table)
            ->where($column, $slug)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
