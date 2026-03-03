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

    #[ORM\OneToOne(mappedBy: 'monthlyBox', cascade: ['persist', 'remove'])]
    private ?Order $table_order = null;

    #[ORM\ManyToOne(inversedBy: 'monthlyBoxes')]
    private ?Subscription $subscription = null;

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

    public function getTableOrder(): ?Order
    {
        return $this->table_order;
    }

    public function setTableOrder(?Order $table_order): static
    {
        // unset the owning side of the relation if necessary
        if ($table_order === null && $this->table_order !== null) {
            $this->table_order->setMonthlyBox(null);
        }

        // set the owning side of the relation if necessary
        if ($table_order !== null && $table_order->getMonthlyBox() !== $this) {
            $table_order->setMonthlyBox($this);
        }

        $this->table_order = $table_order;

        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): static
    {
        $this->subscription = $subscription;

        return $this;
    }
}
