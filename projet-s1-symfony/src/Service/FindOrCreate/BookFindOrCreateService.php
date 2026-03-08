<?php

namespace App\Service\FindOrCreate;

use App\Entity\Book;
use App\Entity\BookPublisher;
use App\Entity\Work;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class BookFindOrCreateService extends AbstractFindOrCreateService
{
    public function __construct(
        private readonly BookRepository $bookRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($serializer, $entityManager);
    }

    /**
     * @param array<string, mixed> $bookData
     */
    public function findOrCreateFromPayload(array $bookData, Work $work, ?BookPublisher $publisher): Book
    {
        $bookData['resolvedWork']      = $work;
        $bookData['resolvedPublisher'] = $publisher;

        /** @var Book $book */
        $book = $this->findOrCreate(
            $bookData,
            [
                'work'            => $work,
                'publicationDate' => isset($bookData['publicationDate'])
                    ? new \DateTime($bookData['publicationDate'])
                    : null,
            ],
            Book::class,
            $this->bookRepository,
        );

        return $book;
    }

    protected function getSerializableData(array $data): array
    {
        return array_diff_key($data, array_flip(['resolvedWork', 'resolvedPublisher']));
    }

    protected function afterCreate(object $entity, array $data): void
    {
        /** @var Book $entity */
        $entity->setWork($data['resolvedWork']);

        /** @var BookPublisher|null $publisher */
        $publisher = $data['resolvedPublisher'] ?? null;
        if ($publisher) {
            $entity->setBookPublisher($publisher);
        }
    }
}

