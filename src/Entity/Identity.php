<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\IdentityKindEnum;
use App\Repository\IdentityRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: IdentityRepository::class)]

class Identity
{
    public const KINSHIP_FATHER = 1;

    public const KINSHIP_MOTHER = 2;

    public const KINSHIP_GUARDIANSHIP = 3;

    public const KINSHIP_OTHER = 4;

    public const KINSHIPS = [
        self::KINSHIP_FATHER => 'identity.kinship.father',
        self::KINSHIP_MOTHER => 'identity.kinship.mother',
        self::KINSHIP_GUARDIANSHIP => 'identity.kinship.guardianship',
        self::KINSHIP_OTHER => 'identity.kinship.other',
    ];

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[Column(type: 'string', length: 100, nullable: true)]
    private ?string $name = null;

    #[Column(type: 'string', length: 100, nullable: true)]
    private ?string $firstName = null;

    #[Column(type: 'date', nullable: true)]
    private ?DateTimeInterface $birthDate = null;

    #[Column(type: 'string', length: 100, nullable: true)]
    private ?string $birthPlace = null;

    #[Column(type: 'string', length: 14, nullable: true)]
    private ?string $phone = null;

    #[Column(type: 'string', length: 14, nullable: true)]
    private ?string $mobile = null;

    #[Column(type: 'string', length: 100, nullable: true)]
    private ?string $profession = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $kinship = null;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'identities')]
    private ?User $user;

    #[Column(type: 'string', length: 100, nullable: true)]
    private ?string $email = null;

    #[Column(type: 'string', length: 100, nullable: true)]
    private ?string $picture = null;

    #[ManyToOne(targetEntity: Address::class, inversedBy: 'identities', cascade: ['persist'])]
    private $address;

    #[Column(type: 'IdentityKind', options: ['default' => IdentityKindEnum::MEMBER])]
    private IdentityKindEnum $kind = IdentityKindEnum::MEMBER;

    #[ManyToOne(targetEntity: Commune::class, inversedBy: 'identities')]
    private ?Commune $birthCommune = null;

    #[Column(type: 'string', length: 14, nullable: true)]
    private ?string $emergencyPhone = null;

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

    public function getKinship(): ?int
    {
        return $this->kinship;
    }

    public function setKinship(?int $kinship): self
    {
        $this->kinship = $kinship;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function getAddress()
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

    public function getKind(): IdentityKindEnum
    {
        return $this->kind;
    }

    public function setKind(IdentityKindEnum $kind): self
    {
        $this->kind = $kind;

        return $this;
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
}
