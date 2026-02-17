<?php

namespace App\Mcp\Tool;

use App\Repository\WorkRepository;
use Mcp\Capability\Attribute\McpTool;

#[McpTool(
    name: 'get_work',
    description: 'Récupère les détails d\'une œuvre littéraire spécifique par son ID, incluant ses auteurs, genres et livres disponibles'
)]
final class GetWorkTool
{
    public function __construct(
        private readonly WorkRepository $workRepository
    ) {
    }

    public function __invoke(
        int $id
    ): array {
        $work = $this->workRepository->find($id);

        if (!$work) {
            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode(['error' => 'Œuvre non trouvée'], JSON_UNESCAPED_UNICODE)
                    ]
                ],
                'isError' => true
            ];
        }

        $data = [
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


