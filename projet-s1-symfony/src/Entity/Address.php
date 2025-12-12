<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(length: 255)]
    private ?string $recipientName = null;

    #[ORM\Column(length: 255)]
    private ?string $street = null;

    #[ORM\Column(length: 255)]
    private ?string $addressDetails = null;

    #[ORM\Column(length: 255)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'address')]
    private Collection $orders;

    /**
     * @var Collection<int, Customer>
     */
    #[ORM\OneToMany(targetEntity: Customer::class, mappedBy: 'address')]
    private Collection $customer;

    /**
     * @var Collection<int, Subscription>
     */
    #[ORM\OneToMany(targetEntity: Subscription::class, mappedBy: 'address')]
    private Collection $subscription;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->customer = new ArrayCollection();
        $this->subscription = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getRecipientName(): ?string
    {
        return $this->recipientName;
    }

    public function setRecipientName(string $recipientName): static
    {
        $this->recipientName = $recipientName;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getAddressDetails(): ?string
    {
        return $this->addressDetails;
    }

    public function setAddressDetails(string $addressDetails): static
    {
        $this->addressDetails = $addressDetails;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setAddress($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getAddress() === $this) {
                $order->setAddress(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Customer>
     */
    public function getCustomer(): Collection
    {
        return $this->customer;
    }

    public function addCustomer(Customer $customer): static
    {
        if (!$this->customer->contains($customer)) {
            $this->customer->add($customer);
            $customer->setAddress($this);
        }

        return $this;
    }

    public function removeCustomer(Customer $customer): static
    {
        if ($this->customer->removeElement($customer)) {
            // set the owning side to null (unless already changed)
            if ($customer->getAddress() === $this) {
                $customer->setAddress(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function getSubscription(): Collection
    {
        return $this->subscription;
    }

    public function addSubscription(Subscription $subscription): static
    {
        if (!$this->subscription->contains($subscription)) {
            $this->subscription->add($subscription);
            $subscription->setAddress($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): static
    {
        if ($this->subscription->removeElement($subscription)) {
            // set the owning side to null (unless already changed)
            if ($subscription->getAddress() === $this) {
                $subscription->setAddress(null);
            }
        }

        return $this;
    }
}
