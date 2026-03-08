<?php

namespace App\Service\FindOrCreate;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Template Method pattern.
 *
 * Provides the common find-or-create skeleton:
 *   1. Search in the repository using the given criteria
 *   2. If found → return
 *   3. If not → deserialize getSerializableData(), call afterCreate() hook, persist, return
 */
abstract class AbstractFindOrCreateService
{
    public function __construct(
        protected readonly SerializerInterface $serializer,
        protected readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * @param array<string, mixed>  $data
     * @param array<string, mixed>  $criteria  Fields used to search for an existing entity
     * @param class-string          $class     Target entity FQCN
     * @param ObjectRepository<object> $repository
     */
    final protected function findOrCreate(array $data, array $criteria, string $class, ObjectRepository $repository): object
    {
        $existing = $repository->findOneBy($criteria);

        if ($existing) {
            return $existing;
        }

        $entity = $this->serializer->deserialize(
            json_encode($this->getSerializableData($data)),
            $class,
            'json'
        );

        $this->afterCreate($entity, $data);

        $this->entityManager->persist($entity);

        return $entity;
    }

    /**
     * Override to strip keys that must not be passed to the deserializer.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function getSerializableData(array $data): array
    {
        return $data;
    }

    /**
     * Hook called after instantiation but before persist().
     * Override to attach relations or set computed fields.
     *
     * @param array<string, mixed> $data
     */
    protected function afterCreate(object $entity, array $data): void
    {
        // no-op by default
    }
}


