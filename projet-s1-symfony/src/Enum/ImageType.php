<?php

namespace App\Enum;

enum ImageType {
    case BOOK;
    case AUTHOR;

    public function getSuffix(): string
    {
        return match($this) {
            ImageType::BOOK => 'b',
            ImageType::AUTHOR => 'a',
        };
    }
}
