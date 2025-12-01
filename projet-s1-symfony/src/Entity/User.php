<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $FirstName = null;

    #[ORM\Column(length: 255)]
    private ?string $LastName = null;

    #[ORM\Column(length: 255)]
    private ?string $Email = null;

    #[ORM\Column(length: 255)]
    private ?string $Password = null;

    #[ORM\Column]
    private ?\DateTime $InscriptionDate = null;

    #[ORM\Column]
    private ?\DateTime $LastConnectionDate = null;

    /**
     * @var Collection<int, Command>
     */
    #[ORM\OneToMany(targetEntity: Command::class, mappedBy: 'buyer')]
    private Collection $Commands;

    #[ORM\ManyToOne(inversedBy: 'Owner')]
    private ?Subscription $subscription = null;

    public function __construct()
    {
        $this->Commands = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->FirstName;
    }

    public function setFirstName(string $FirstName): static
    {
        $this->FirstName = $FirstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->LastName;
    }

    public function setLastName(string $LastName): static
    {
        $this->LastName = $LastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): static
    {
        $this->Email = $Email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->Password;
    }

    public function setPassword(string $Password): static
    {
        $this->Password = $Password;

        return $this;
    }

    public function getInscriptionDate(): ?\DateTime
    {
        return $this->InscriptionDate;
    }

    public function setInscriptionDate(\DateTime $InscriptionDate): static
    {
        $this->InscriptionDate = $InscriptionDate;

        return $this;
    }

    public function getLastConnectionDate(): ?\DateTime
    {
        return $this->LastConnectionDate;
    }

    public function setLastConnectionDate(\DateTime $LastConnectionDate): static
    {
        $this->LastConnectionDate = $LastConnectionDate;

        return $this;
    }

    /**
     * @return Collection<int, Command>
     */
    public function getCommands(): Collection
    {
        return $this->Commands;
    }

    public function addCommand(Command $command): static
    {
        if (!$this->Commands->contains($command)) {
            $this->Commands->add($command);
            $command->setBuyer($this);
        }

        return $this;
    }

    public function removeCommand(Command $command): static
    {
        if ($this->Commands->removeElement($command)) {
            // set the owning side to null (unless already changed)
            if ($command->getBuyer() === $this) {
                $command->setBuyer(null);
            }
        }

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
