<?php

namespace App\Entity;

use App\Repository\SubscriptionOfferRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionOfferRepository::class)]
class SubscriptionOffer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $offerName = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $monthlyPrice = null;

    #[ORM\Column]
    private ?int $includedBooksCount = null;

    #[ORM\Column]
    private ?int $commitmentMonths = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOfferName(): ?string
    {
        return $this->offerName;
    }

    public function setOfferName(string $offerName): static
    {
        $this->offerName = $offerName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getMonthlyPrice(): ?int
    {
        return $this->monthlyPrice;
    }

    public function setMonthlyPrice(int $monthlyPrice): static
    {
        $this->monthlyPrice = $monthlyPrice;

        return $this;
    }

    public function getIncludedBooksCount(): ?int
    {
        return $this->includedBooksCount;
    }

    public function setIncludedBooksCount(int $includedBooksCount): static
    {
        $this->includedBooksCount = $includedBooksCount;

        return $this;
    }

    public function getCommitmentMonths(): ?int
    {
        return $this->commitmentMonths;
    }

    public function setCommitmentMonths(int $commitmentMonths): static
    {
        $this->commitmentMonths = $commitmentMonths;

        return $this;
    }
}
