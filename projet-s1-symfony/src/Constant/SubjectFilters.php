<?php

namespace App\Constant;

final class SubjectFilters
{
    /** Subject filters (Amazon-style "Department") - OpenLibrary subject keys */
    public const FILTERS = [
        'fiction' => 'Littérature',
        'science_fiction' => 'Science-Fiction',
        'mystery' => 'Policier / Thriller',
        'love' => 'Romance',
        'fantasy' => 'Fantasy',
        'biography' => 'Biographies',
        'history' => 'Histoire',
        'juvenile' => 'Jeunesse',
        'comics' => 'BD & Mangas',
    ];
}
