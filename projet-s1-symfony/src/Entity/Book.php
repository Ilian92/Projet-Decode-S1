<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(nullable: true)]
    private ?int $currentUnitPrice = null;

    #[ORM\Column(nullable: true)]
    private ?int $availableStock = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coverImageUrl = null;

    #[ORM\Column(nullable: true)]
    private ?int $weightGrams = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $releaseDate = null;

    #[ORM\ManyToOne(inversedBy: 'book')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Work $work = null;

    #[ORM\ManyToOne(inversedBy: 'book')]
    private ?BookPublisher $bookPublisher = null;

    /**
     * @var Collection<int, OrderLine>
     */
    #[ORM\OneToMany(targetEntity: OrderLine::class, mappedBy: 'book')]
    private Collection $orderLine;

    public function __construct()
    {
        $this->orderLine = new ArrayCollection();
    }

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

    public function getWork(): ?Work
    {
        return $this->work;
    }

    public function setWork(?Work $work): static
    {
        $this->work = $work;

        return $this;
    }

    public function getBookPublisher(): ?BookPublisher
    {
        return $this->bookPublisher;
    }

    public function setBookPublisher(?BookPublisher $bookPublisher): static
    {
        $this->bookPublisher = $bookPublisher;

        return $this;
    }

    /**
     * @return Collection<int, OrderLine>
     */
    public function getOrderLine(): Collection
    {
        return $this->orderLine;
    }

    public function addOrderLine(OrderLine $orderLine): static
    {
        if (!$this->orderLine->contains($orderLine)) {
            $this->orderLine->add($orderLine);
            $orderLine->setBook($this);
        }

        return $this;
    }

    public function removeOrderLine(OrderLine $orderLine): static
    {
        if ($this->orderLine->removeElement($orderLine)) {
            // set the owning side to null (unless already changed)
            if ($orderLine->getBook() === $this) {
                $orderLine->setBook(null);
            }
        }

        return $this;
    }
}
