<?php

namespace App\Mcp\Tool;

use App\Repository\AuthorRepository;
use Mcp\Capability\Attribute\McpTool;

#[McpTool(
    name: 'get_author_by_name',
    description: 'Recherche un auteur par son prénom et/ou nom de famille (recherche exacte ou partielle), retourne tous les auteurs correspondants avec leurs œuvres'
)]
final class GetAuthorByNameTool
{
    public function __construct(
        private readonly AuthorRepository $authorRepository
    ) {
    }

    public function __invoke(
        ?string $firstName = null,
        ?string $lastName = null
    ): array {
        if (!$firstName && !$lastName) {
            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode([
                            'error' => 'Vous devez fournir au moins un prénom ou un nom de famille'
                        ], JSON_UNESCAPED_UNICODE)
                    ]
                ],
                'isError' => true
            ];
        }

        $queryBuilder = $this->authorRepository->createQueryBuilder('a');
        $conditions = [];
        $parameters = [];

        if ($firstName && $lastName) {
            // Recherche exacte si les deux sont fournis
            $exactMatch = $this->authorRepository->findOneBy([
                'firstName' => $firstName,
                'lastName' => $lastName
            ]);

            if ($exactMatch) {
                $authors = [$exactMatch];
            } else {
                // Recherche partielle
                $conditions[] = 'a.firstName LIKE :firstName';
                $conditions[] = 'a.lastName LIKE :lastName';
                $parameters['firstName'] = '%' . $firstName . '%';
                $parameters['lastName'] = '%' . $lastName . '%';
            }
        } elseif ($firstName) {
            // Recherche par prénom uniquement
            $conditions[] = 'a.firstName LIKE :firstName';
            $parameters['firstName'] = '%' . $firstName . '%';
        } else {
            // Recherche par nom uniquement
            $conditions[] = 'a.lastName LIKE :lastName';
            $parameters['lastName'] = '%' . $lastName . '%';
        }

        if (!isset($authors)) {
            if (!empty($conditions)) {
                $queryBuilder->where(implode(' AND ', $conditions));
                foreach ($parameters as $key => $value) {
                    $queryBuilder->setParameter($key, $value);
                }
            }
            $authors = $queryBuilder->getQuery()->getResult();
        }

        if (empty($authors)) {
            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode([
                            'error' => 'Aucun auteur trouvé',
                            'searched_firstName' => $firstName,
                            'searched_lastName' => $lastName
                        ], JSON_UNESCAPED_UNICODE)
                    ]
                ],
                'isError' => true
            ];
        }

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
                    'text' => json_encode([
                        'count' => count($data),
                        'searched_firstName' => $firstName,
                        'searched_lastName' => $lastName,
                        'results' => $data
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                ]
            ]
        ];
    }
}

