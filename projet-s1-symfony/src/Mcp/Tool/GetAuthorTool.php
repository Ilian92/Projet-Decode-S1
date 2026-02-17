<?php

namespace App\Mcp\Tool;

use App\Repository\AuthorRepository;
use Mcp\Capability\Attribute\McpTool;

#[McpTool(
    name: 'get_author',
    description: 'Récupère les détails d\'un auteur spécifique par son ID, incluant sa biographie et ses œuvres'
)]
final class GetAuthorTool
{
    public function __construct(
        private readonly AuthorRepository $authorRepository
    ) {
    }

    public function __invoke(
        int $id
    ): array {
        $author = $this->authorRepository->find($id);

        if (!$author) {
            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode(['error' => 'Auteur non trouvé'], JSON_UNESCAPED_UNICODE)
                    ]
                ],
                'isError' => true
            ];
        }

        $data = [
            'id' => $author->getId(),
            'firstName' => $author->getFirstName(),
            'lastName' => $author->getLastName(),
            'biography' => $author->getBiography(),
            'photoUrl' => $author->getPhotoUrl(),
            'works' => array_map(
                fn($work) => [
                    'id' => $work->getId(),
                    'title' => $work->getTitle()
                ],
                $author->getWorks()->toArray()
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


