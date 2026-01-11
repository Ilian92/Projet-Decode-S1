<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $expectedEndDate = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $nextPaymentDate = null;

    #[ORM\ManyToOne(inversedBy: 'subscription')]
    private ?Address $address = null;

    /**
     * @var Collection<int, MonthlyBox>
     */
    #[ORM\OneToMany(targetEntity: MonthlyBox::class, mappedBy: 'subscription')]
    private Collection $monthlyBoxes;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    private ?SubscriptionOffer $subscriptionOffer = null;

    #[ORM\ManyToOne(inversedBy: 'subscriptions')]
    private ?Customer $customer = null;

    public function __construct()
    {
        $this->monthlyBoxes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getExpectedEndDate(): ?\DateTime
    {
        return $this->expectedEndDate;
    }

    public function setExpectedEndDate(\DateTime $expectedEndDate): static
    {
        $this->expectedEndDate = $expectedEndDate;

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

    public function getNextPaymentDate(): ?\DateTime
    {
        return $this->nextPaymentDate;
    }

    public function setNextPaymentDate(\DateTime $nextPaymentDate): static
    {
        $this->nextPaymentDate = $nextPaymentDate;

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

    /**
     * @return Collection<int, MonthlyBox>
     */
    public function getMonthlyBoxes(): Collection
    {
        return $this->monthlyBoxes;
    }

    public function addMonthlyBox(MonthlyBox $monthlyBox): static
    {
        if (!$this->monthlyBoxes->contains($monthlyBox)) {
            $this->monthlyBoxes->add($monthlyBox);
            $monthlyBox->setSubscription($this);
        }

        return $this;
    }

    public function removeMonthlyBox(MonthlyBox $monthlyBox): static
    {
        if ($this->monthlyBoxes->removeElement($monthlyBox)) {
            // set the owning side to null (unless already changed)
            if ($monthlyBox->getSubscription() === $this) {
                $monthlyBox->setSubscription(null);
            }
        }

        return $this;
    }

    public function getSubscriptionOffer(): ?SubscriptionOffer
    {
        return $this->subscriptionOffer;
    }

    public function setSubscriptionOffer(?SubscriptionOffer $subscriptionOffer): static
    {
        $this->subscriptionOffer = $subscriptionOffer;

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
