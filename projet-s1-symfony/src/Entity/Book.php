<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $publicationDate = null;

    #[ORM\Column]
    private ?int $currentUnitPrice = null;

    #[ORM\Column]
    private ?int $availableStock = null;

    #[ORM\Column(length: 255)]
    private ?string $coverImageUrl = null;

    #[ORM\Column]
    private ?int $weightGrams = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $releaseDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicationDate(): ?\DateTime
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(\DateTime $publicationDate): static
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getCurrentUnitPrice(): ?int
    {
        return $this->currentUnitPrice;
    }

    public function setCurrentUnitPrice(int $currentUnitPrice): static
    {
        $this->currentUnitPrice = $currentUnitPrice;

        return $this;
    }

    public function getAvailableStock(): ?int
    {
        return $this->availableStock;
    }

    public function setAvailableStock(int $availableStock): static
    {
        $this->availableStock = $availableStock;

        return $this;
    }

    public function getCoverImageUrl(): ?string
    {
        return $this->coverImageUrl;
    }

    public function setCoverImageUrl(string $coverImageUrl): static
    {
        $this->coverImageUrl = $coverImageUrl;

        return $this;
    }

    public function getWeightGrams(): ?int
    {
        return $this->weightGrams;
    }

    public function setWeightGrams(int $weightGrams): static
    {
        $this->weightGrams = $weightGrams;

        return $this;
    }

    public function getReleaseDate(): ?\DateTime
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(\DateTime $releaseDate): static
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }
}
