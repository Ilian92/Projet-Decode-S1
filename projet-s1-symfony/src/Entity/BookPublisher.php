<?php

namespace App\Entity;

use App\Repository\BookPublisherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookPublisherRepository::class)]
class BookPublisher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $publisherName = null;

    #[ORM\Column(length: 255)]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 255)]
    private ?string $website = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\OneToMany(targetEntity: Book::class, mappedBy: 'bookPublisher')]
    private Collection $book;

    public function __construct()
    {
        $this->book = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublisherName(): ?string
    {
        return $this->publisherName;
    }

    public function setPublisherName(string $publisherName): static
    {
        $this->publisherName = $publisherName;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): static
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): static
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBook(): Collection
    {
        return $this->book;
    }

    public function addBook(Book $book): static
    {
        if (!$this->book->contains($book)) {
            $this->book->add($book);
            $book->setBookPublisher($this);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        if ($this->book->removeElement($book)) {
            // set the owning side to null (unless already changed)
            if ($book->getBookPublisher() === $this) {
                $book->setBookPublisher(null);
            }
        }

        return $this;
    }
}
