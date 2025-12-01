<?php

namespace App\Entity;

use App\Repository\SubscriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
class Subscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'subscription')]
    private Collection $Owner;

    public function __construct()
    {
        $this->Owner = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getOwner(): Collection
    {
        return $this->Owner;
    }

    public function addOwner(User $owner): static
    {
        if (!$this->Owner->contains($owner)) {
            $this->Owner->add($owner);
            $owner->setSubscription($this);
        }

        return $this;
    }

    public function removeOwner(User $owner): static
    {
        if ($this->Owner->removeElement($owner)) {
            // set the owning side to null (unless already changed)
            if ($owner->getSubscription() === $this) {
                $owner->setSubscription(null);
            }
        }

        return $this;
    }
}
