<?php

namespace App\Entity;

use App\Repository\MonthlyBoxRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MonthlyBoxRepository::class)]
class MonthlyBox
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $referenceMonth = null;

    #[ORM\Column]
    private ?\DateTime $creationDate = null;

    #[ORM\Column(length: 255)]
    private ?string $shippingStatus = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReferenceMonth(): ?string
    {
        return $this->referenceMonth;
    }

    public function setReferenceMonth(string $referenceMonth): static
    {
        $this->referenceMonth = $referenceMonth;

        return $this;
    }

    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getShippingStatus(): ?string
    {
        return $this->shippingStatus;
    }

    public function setShippingStatus(string $shippingStatus): static
    {
        $this->shippingStatus = $shippingStatus;

        return $this;
    }
}
