<?php

namespace App\Entity;

use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $orderDate = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?int $totalAmount = null;

    #[ORM\Column]
    private ?int $shippingCost = null;

    #[ORM\Column(length: 255)]
    private ?string $trackingNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripePaymentIntentId = null;

    /**
     * @var Collection<int, OrderLine>
     */
    #[ORM\OneToMany(targetEntity: OrderLine::class, mappedBy: 'tableOrder')]
    private Collection $orderLine;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Address $address = null;

    #[ORM\OneToOne(inversedBy: 'table_order', cascade: ['persist', 'remove'])]
    private ?MonthlyBox $monthlyBox = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Customer $customer = null;

    public function __construct()
    {
        $this->orderLine = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderDate(): ?\DateTime
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTime $orderDate): static
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isFulfillable(): bool
    {
        $demandByBook = [];
        $booksById = [];
        foreach ($this->orderLine as $line) {
            $book = $line->getBook();
            if ($book === null) {
                return false;
            }
            $id = $book->getId();
            $demandByBook[$id] = ($demandByBook[$id] ?? 0) + $line->getQuantity();
            $booksById[$id] = $book;
        }
        foreach ($demandByBook as $bookId => $quantity) {
            $book = $booksById[$bookId] ?? null;
            if ($book === null) {
                return false;
            }
            $available = $book->getAvailableStock() ?? 0;
            if ($available < $quantity) {
                return false;
            }
        }

        return true;
    }

    public function getTotalAmount(): ?int
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(int $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getShippingCost(): ?int
    {
        return $this->shippingCost;
    }

    public function setShippingCost(int $shippingCost): static
    {
        $this->shippingCost = $shippingCost;

        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(string $trackingNumber): static
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
    }

    public function getStripePaymentIntentId(): ?string
    {
        return $this->stripePaymentIntentId;
    }

    public function setStripePaymentIntentId(?string $stripePaymentIntentId): static
    {
        $this->stripePaymentIntentId = $stripePaymentIntentId;

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
            $orderLine->setTableOrder($this);
        }

        return $this;
    }

    public function removeOrderLine(OrderLine $orderLine): static
    {
        if ($this->orderLine->removeElement($orderLine)) {
            // set the owning side to null (unless already changed)
            if ($orderLine->getTableOrder() === $this) {
                $orderLine->setTableOrder(null);
            }
        }

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getMonthlyBox(): ?MonthlyBox
    {
        return $this->monthlyBox;
    }

    public function setMonthlyBox(?MonthlyBox $monthlyBox): static
    {
        $this->monthlyBox = $monthlyBox;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }
}
