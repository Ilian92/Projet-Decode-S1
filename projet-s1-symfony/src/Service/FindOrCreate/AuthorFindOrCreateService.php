<?php

namespace App\Service\FindOrCreate;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AuthorFindOrCreateService extends AbstractFindOrCreateService
{
    public function __construct(
        private readonly AuthorRepository $authorRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($serializer, $entityManager);
    }

    /**
     * @param array{firstName: string, lastName: string, biography?: string, photoUrl?: string} $data
     */
    public function findOrCreateAuthor(array $data): Author
    {
        /** @var Author $author */
        $author = $this->findOrCreate(
            $data,
            ['firstName' => $data['firstName'], 'lastName' => $data['lastName']],
            Author::class,
            $this->authorRepository,
        );

        return $author;
    }
}
