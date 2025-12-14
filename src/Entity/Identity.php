<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\IdentityRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IdentityRepository::class)]

class Identity
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $birthDate = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $birthPlace = null;

    #[ORM\Column(type: 'string', length: 14, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 14, nullable: true)]
    private ?string $mobile = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $profession = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $picture = null;

    #[ORM\ManyToOne(targetEntity: Address::class, inversedBy: 'identities', cascade: ['persist'])]
    private $address;

    #[ORM\ManyToOne(targetEntity: Commune::class, inversedBy: 'identities')]
    private ?Commune $birthCommune = null;

    #[ORM\Column(type: 'string', length: 14, nullable: true)]
    private ?string $emergencyPhone = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $birthCountry = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emergencyContact = null;

    /**
     * @var Collection<int, UserGardian>
     */
    #[ORM\OneToMany(targetEntity: UserGardian::class, mappedBy: 'identity')]
    private Collection $userGardians;

    #[ORM\OneToOne(inversedBy: 'identity', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    public function __construct()
    {
        $this->userGardians = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getFullName(): string
    {
        return ($this->getName() && $this->getFirstName())
            ? sprintf('%s %s', mb_strtoupper($this->getName()), mb_ucfirst($this->getFirstName()))
            : '';
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getBirthPlace(): ?string
    {
        return $this->birthPlace;
    }

    public function setBirthPlace(?string $birthPlace): self
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): self
    {
        $this->profession = $profession;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function hasAddress(): bool
    {
        return (null !== $this->address) ? !$this->address->isEmpty() : false;
    }

    public function isEmpty()
    {
        return null === $this->name && null === $this->firstName;
    }

    public function getBirthCommune(): ?Commune
    {
        return $this->birthCommune;
    }

    public function setBirthCommune(?Commune $birthCommune): self
    {
        $this->birthCommune = $birthCommune;

        return $this;
    }

    public function getEmergencyPhone(): ?string
    {
        return $this->emergencyPhone;
    }

    public function setEmergencyPhone(?string $emergencyPhone): self
    {
        $this->emergencyPhone = $emergencyPhone;

        return $this;
    }

    public function getBirthCountry(): ?string
    {
        return $this->birthCountry;
    }

    public function setBirthCountry(?string $birthCountry): static
    {
        $this->birthCountry = $birthCountry;

        return $this;
    }

    public function getEmergencyContact(): ?string
    {
        return $this->emergencyContact;
    }

    public function setEmergencyContact(?string $emergencyContact): static
    {
        $this->emergencyContact = $emergencyContact;

        return $this;
    }

    /**
     * @return Collection<int, UserGardian>
     */
    public function getUserGardians(): Collection
    {
        return $this->userGardians;
    }

    public function addUserGardian(UserGardian $userGardian): static
    {
        if (!$this->userGardians->contains($userGardian)) {
            $this->userGardians->add($userGardian);
            $userGardian->setIdentity($this);
        }

        return $this;
    }

    public function removeUserGardian(UserGardian $userGardian): static
    {
        if ($this->userGardians->removeElement($userGardian)) {
            // set the owning side to null (unless already changed)
            if ($userGardian->getIdentity() === $this) {
                $userGardian->setIdentity(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
