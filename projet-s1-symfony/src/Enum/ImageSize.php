<?php

namespace App\Enum;

enum ImageSize {
    case SMALL;
    case MEDIUM;
    case LARGE;

    public function getSuffix(): string
    {
        return match($this) {
            ImageSize::SMALL => 'S',
            ImageSize::MEDIUM => 'M',
            ImageSize::LARGE => 'L',
        };
    }
}
