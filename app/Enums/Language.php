<?php

namespace App\Enums;

enum Language: string
{
    case Urdu = 'ur';
    case English = 'en';

    public function label(): string
    {
        return match ($this) {
            self::Urdu => 'Urdu',
            self::English => 'English',
        };
    }
}
