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

    #[ORM\Column(length: 255)]
    private ?string $Isbn = null;

    #[ORM\Column(length: 255)]
    private ?string $Title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $PublishDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $Price = null;

    #[ORM\Column]
    private ?int $AmountInStock = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $Weight = null;

    #[ORM\ManyToOne(inversedBy: 'books')]
    private ?Author $Author = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsbn(): ?string
    {
        return $this->Isbn;
    }

    public function setIsbn(string $Isbn): static
    {
        $this->Isbn = $Isbn;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): static
    {
        $this->Title = $Title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): static
    {
        $this->Description = $Description;

        return $this;
    }

    public function getPublishDate(): ?\DateTime
    {
        return $this->PublishDate;
    }

    public function setPublishDate(\DateTime $PublishDate): static
    {
        $this->PublishDate = $PublishDate;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->Price;
    }

    public function setPrice(string $Price): static
    {
        $this->Price = $Price;

        return $this;
    }

    public function getAmountInStock(): ?int
    {
        return $this->AmountInStock;
    }

    public function setAmountInStock(int $AmountInStock): static
    {
        $this->AmountInStock = $AmountInStock;

        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->Weight;
    }

    public function setWeight(string $Weight): static
    {
        $this->Weight = $Weight;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->Author;
    }

    public function setAuthor(?Author $Author): static
    {
        $this->Author = $Author;

        return $this;
    }
}
