<?php

namespace App\Service\FindOrCreate;

use App\Entity\BookPublisher;
use App\Repository\BookPublisherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class BookPublisherFindOrCreateService extends AbstractFindOrCreateService
{
    public function __construct(
        private readonly BookPublisherRepository $bookPublisherRepository,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($serializer, $entityManager);
    }

    /**
     * @param array{publisherName: string, contactEmail?: string, website?: string} $data
     */
    public function findOrCreatePublisher(array $data): BookPublisher
    {
        /** @var BookPublisher $publisher */
        $publisher = $this->findOrCreate(
            $data,
            ['publisherName' => $data['publisherName']],
            BookPublisher::class,
            $this->bookPublisherRepository,
        );

        return $publisher;
    }
}
