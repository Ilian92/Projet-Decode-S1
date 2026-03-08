<?php

namespace App\Service\FindOrCreate;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class GenreFindOrCreateService extends AbstractFindOrCreateService
{
    public function __construct(
        private readonly GenreRepository $genreRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($serializer, $entityManager);
    }

    /**
     * @param array{label: string} $data
     */
    public function findOrCreateGenre(array $data): Genre
    {
        /** @var Genre $genre */
        $genre = $this->findOrCreate(
            $data,
            ['label' => $data['label']],
            Genre::class,
            $this->genreRepository,
        );

        return $genre;
    }
}
