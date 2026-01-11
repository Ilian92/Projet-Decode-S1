<?php

namespace App\Entity;

use App\Repository\OrderLineRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderLineRepository::class)]
class OrderLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?int $unitPriceSnapshot = null;

    #[ORM\ManyToOne(inversedBy: 'orderLine')]
    private ?Book $book = null;

    #[ORM\ManyToOne(inversedBy: 'orderLine')]
    private ?Order $tableOrder = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPriceSnapshot(): ?int
    {
        return $this->unitPriceSnapshot;
    }

    public function setUnitPriceSnapshot(int $unitPriceSnapshot): static
    {
        $this->unitPriceSnapshot = $unitPriceSnapshot;

        return $this;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(?Book $book): static
    {
        $this->book = $book;

        return $this;
    }

    public function getTableOrder(): ?Order
    {
        return $this->tableOrder;
    }

    public function setTableOrder(?Order $tableOrder): static
    {
        $this->tableOrder = $tableOrder;

        return $this;
    }
}
