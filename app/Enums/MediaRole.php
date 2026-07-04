<?php

namespace App\Enums;

enum MediaRole: string
{
    case Featured = 'featured';
    case Gallery = 'gallery';
    case Inline = 'inline';
    case Attachment = 'attachment';
    case Thumbnail = 'thumbnail';

    public function label(): string
    {
        return match ($this) {
            self::Featured => 'Featured Image',
            self::Gallery => 'Gallery Image',
            self::Inline => 'Inline Media',
            self::Attachment => 'Attachment',
            self::Thumbnail => 'Thumbnail',
        };
    }
}
