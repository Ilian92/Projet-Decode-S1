<?php

namespace App\Mcp\Tool;

use App\Repository\AuthorRepository;
use Mcp\Capability\Attribute\McpTool;
//use Symfony\AI\McpBundle\Attribute\AsTool;

#[McpTool(
    name: 'get_authors',
    description: 'Récupère la liste de tous les auteurs avec leurs informations et leurs œuvres'
)]
final class GetAuthorsTool
{
    public function __construct(
        private readonly AuthorRepository $authorRepository
    ) {
    }

    public function __invoke(): array
    {
        $authors = $this->authorRepository->findAll();

        $data = [];
        foreach ($authors as $author) {
            $data[] = [
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



