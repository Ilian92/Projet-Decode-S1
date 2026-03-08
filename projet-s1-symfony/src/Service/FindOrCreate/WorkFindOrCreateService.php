<?php

namespace App\Service\FindOrCreate;

use App\Entity\Author;
use App\Entity\Genre;
use App\Entity\Work;
use App\Repository\WorkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class WorkFindOrCreateService extends AbstractFindOrCreateService
{
    public function __construct(
        private readonly WorkRepository $workRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($serializer, $entityManager);
    }

    /**
     * @param array<string, mixed>   $workData
     * @param array<int, Author>     $authors
     * @param array<int, Genre>      $genres
     */
    public function findOrCreateFromPayload(array $workData, array $authors, array $genres): Work
    {
        $authorIds = $workData['authorIds'] ?? [];
        $genreIds  = $workData['genreIds']  ?? [];

        $payload = array_diff_key($workData, array_flip(['authorIds', 'genreIds']));
        $payload['resolvedAuthors'] = array_values(array_filter(
            array_map(fn(int $i) => $authors[$i] ?? null, $authorIds)
        ));
        $payload['resolvedGenres'] = array_values(array_filter(
            array_map(fn(int $i) => $genres[$i] ?? null, $genreIds)
        ));

        /** @var Work $work */
        $work = $this->findOrCreate(
            $payload,
            ['title' => $payload['title']],
            Work::class,
            $this->workRepository,
        );

        return $work;
    }

    protected function getSerializableData(array $data): array
    {
        return array_diff_key($data, array_flip(['resolvedAuthors', 'resolvedGenres']));
    }

    protected function afterCreate(object $entity, array $data): void
    {
        /** @var Work $entity */
        foreach ($data['resolvedAuthors'] ?? [] as $author) {
            $entity->addAuthor($author);
        }

        foreach ($data['resolvedGenres'] ?? [] as $genre) {
            $entity->addGenre($genre);
        }
    }
}

