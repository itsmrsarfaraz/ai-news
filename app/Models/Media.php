<?php

namespace App\Models;

use App\Enums\MediaType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'uploaded_by',
    'disk',
    'path',
    'type',
    'mime_type',
    'size',
    'width',
    'height',
    'alt_text',
    'caption',
    'metadata',
])]
class Media extends Model
{
    protected $table = 'media';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_media')
            ->withPivot(['role', 'order', 'caption_override'])
            ->withTimestamps();
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
