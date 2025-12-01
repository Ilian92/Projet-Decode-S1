<?php

namespace App\Entity;

use App\Repository\CommandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandRepository::class)]
class Command
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $CommandDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $TotalCost = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $TotalShippingCost = null;

    #[ORM\ManyToOne(inversedBy: 'Commands')]
    private ?User $buyer = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\ManyToMany(targetEntity: Book::class)]
    private Collection $Books;

    public function __construct()
    {
        $this->Books = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommandDate(): ?\DateTime
    {
        return $this->CommandDate;
    }

    public function setCommandDate(\DateTime $CommandDate): static
    {
        $this->CommandDate = $CommandDate;

        return $this;
    }

    public function getTotalCost(): ?string
    {
        return $this->TotalCost;
    }

    public function setTotalCost(string $TotalCost): static
    {
        $this->TotalCost = $TotalCost;

        return $this;
    }

    public function getTotalShippingCost(): ?string
    {
        return $this->TotalShippingCost;
    }

    public function setTotalShippingCost(string $TotalShippingCost): static
    {
        $this->TotalShippingCost = $TotalShippingCost;

        return $this;
    }

    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    public function setBuyer(?User $buyer): static
    {
        $this->buyer = $buyer;

        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->Books;
    }

    public function addBook(Book $book): static
    {
        if (!$this->Books->contains($book)) {
            $this->Books->add($book);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        $this->Books->removeElement($book);

        return $this;
    }
}
