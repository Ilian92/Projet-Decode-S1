<?php

namespace App\Service;

use App\Service\FindOrCreate\AuthorFindOrCreateService;
use App\Service\FindOrCreate\BookFindOrCreateService;
use App\Service\FindOrCreate\BookPublisherFindOrCreateService;
use App\Service\FindOrCreate\GenreFindOrCreateService;
use App\Service\FindOrCreate\WorkFindOrCreateService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Facade orchestrating the insertion of all book-related entities.
 * Delegates each entity's find-or-create logic to a dedicated service.
 */
class BookRelatedDBInsertionService
{
    public function __construct(
        private readonly AuthorFindOrCreateService $authorService,
        private readonly GenreFindOrCreateService $genreService,
        private readonly BookPublisherFindOrCreateService $publisherService,
        private readonly WorkFindOrCreateService $workService,
        private readonly BookFindOrCreateService $bookService,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Processes a full book insertion payload and returns the resulting entity IDs.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function processInsertion(array $data): array
    {
        return $this->entityManager->wrapInTransaction(function () use ($data): array {
            $authors = array_map(
                fn(array $authorData) => $this->authorService->findOrCreateAuthor($authorData),
                $data['authors'] ?? []
            );

            $genres = array_map(
                fn(array $genreData) => $this->genreService->findOrCreateGenre($genreData),
                $data['genres'] ?? []
            );

            $bookPublisher = isset($data['bookPublisher'])
                ? $this->publisherService->findOrCreatePublisher($data['bookPublisher'])
                : null;

            $work = $this->workService->findOrCreateFromPayload($data['work'], $authors, $genres);

            $book = $this->bookService->findOrCreateFromPayload($data['book'], $work, $bookPublisher);

            return [
                'authors'       => array_map(fn($a) => $a->getId(), $authors),
                'genres'        => array_map(fn($g) => $g->getId(), $genres),
                'work'          => $work->getId(),
                'bookPublisher' => $bookPublisher?->getId(),
                'book'          => $book->getId(),
            ];
        });
    }
}
