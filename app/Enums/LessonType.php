<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LessonType: string implements HasLabel
{
    case Video = 'video';
    case Text = 'text';

    public function getLabel(): string
    {
        return match ($this) {
            self::Video => 'Video',
            self::Text => 'Text',
        };
    }
}
