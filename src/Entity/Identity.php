<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use App\Repository\IdentityRepository;

/**
 * @ORM\Entity(repositoryClass=IdentityRepository::class)
 */
class Identity
{
    public const TYPE_MEMBER = 1;
    public const TYPE_KINSHIP = 2;
    public const TYPE_SECOND_CONTACT = 3;

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

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $firstName;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthDate;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $birthplace;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $mobile;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $profession;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $kinship;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="identities")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $picture;

    /**
     * @ORM\ManyToOne(targetEntity=Address::class, inversedBy="identities", cascade={"persist"})
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $birthDepartment;

    /**
     * @ORM\Column(type="integer", options={"default":1}))
     */
    private $type;


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

    public function getBirthplace(): ?string
    {
        return $this->birthplace;
    }

    public function setBirthplace(?string $birthplace): self
    {
        $this->birthplace = $birthplace;

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

    public function getBirthDepartment(): ?string
    {
        return $this->birthDepartment;
    }

    public function setBirthDepartment(?string $birthDepartment): self
    {
        $this->birthDepartment = $birthDepartment;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }
}
