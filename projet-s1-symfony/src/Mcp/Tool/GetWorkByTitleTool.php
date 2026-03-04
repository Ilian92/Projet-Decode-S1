<?php

namespace App\Mcp\Tool;

use App\Repository\WorkRepository;
use Mcp\Capability\Attribute\McpTool;

#[McpTool(
    name: 'get_work_by_title',
    description: 'Recherche une œuvre littéraire par son titre (recherche exacte ou partielle), retourne toutes les œuvres correspondantes avec leurs auteurs, genres et livres disponibles'
)]
final class GetWorkByTitleTool
{
    public function __construct(
        private readonly WorkRepository $workRepository
    ) {
    }

    public function __invoke(
        string $title
    ): array {
        // Recherche par titre exact
        $exactMatch = $this->workRepository->findOneBy(['title' => $title]);

        if ($exactMatch) {
            $works = [$exactMatch];
        } else {
            // Recherche partielle si pas de correspondance exacte
            $works = $this->workRepository->createQueryBuilder('w')
                ->where('w.title LIKE :title')
                ->setParameter('title', '%' . $title . '%')
                ->getQuery()
                ->getResult();
        }

        if (empty($works)) {
            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode([
                            'error' => 'Aucune œuvre trouvée avec ce titre',
                            'searched_title' => $title
                        ], JSON_UNESCAPED_UNICODE)
                    ]
                ],
                'isError' => true
            ];
        }

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
                    'text' => json_encode([
                        'count' => count($data),
                        'searched_title' => $title,
                        'results' => $data
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                ]
            ]
        ];
    }
}

