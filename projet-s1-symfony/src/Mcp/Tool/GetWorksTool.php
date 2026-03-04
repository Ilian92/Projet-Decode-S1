<?php

namespace App\Mcp\Tool;

use App\Repository\WorkRepository;
use Mcp\Capability\Attribute\McpTool;

#[McpTool(
    name: 'get_works',
    description: 'Récupère la liste de toutes les œuvres littéraires avec leurs auteurs, genres et livres disponibles'
)]
final class GetWorksTool
{
    public function __construct(
        private readonly WorkRepository $workRepository
    ) {
    }

    public function __invoke(): array
    {
        $works = $this->workRepository->findAll();

        $data = [];
        foreach ($works as $work) {
            $data[] = [
                'id' => $work->getId(),
                'title' => $work->getTitle(),
                'summary' => $work->getSummary(),
                'genres' => array_map(
                    fn($genre) => [
                        'id' => $genre->getId(),
                        'label' => $genre->getLabel()
                    ],
                    $work->getGenre()->toArray()
                ),
                'authors' => array_map(
                    fn($author) => [
                        'id' => $author->getId(),
                        'firstName' => $author->getFirstName(),
                        'lastName' => $author->getLastName()
                    ],
                    $work->getAuthor()->toArray()
                ),
                'books' => array_map(
                    fn($book) => [
                        'id' => $book->getId(),
                        'publicationDate' => $book->getPublicationDate()?->format('Y-m-d'),
                        'currentUnitPrice' => $book->getCurrentUnitPrice(),
                        'availableStock' => $book->getAvailableStock()
                    ],
                    $work->getBook()->toArray()
                )
            ];
        }

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                ]
            ]
        ];
    }
}


