<?php

namespace App\Entity;

use App\Repository\LicenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LicenceRepository::class)
 */
class Licence
{
    private const TYPE_RIDE = 1;
    private const TYPE_HIKE = 2;
    private const TYPE_SPORT = 3;

    public const TYPES = [
        self::TYPE_RIDE => 'licence.type.ride',
        self::TYPE_HIKE => 'licence.type.hike',
        self::TYPE_SPORT => 'licence.type.sport',
    ];

    private const COVERAGE_MINI_GEAR = 1;
    private const COVERAGE_SMALL_GEAR  = 2;
    private const COVERAGE_HIGH_GEAR  = 3;

    public const COVERAGES = [
        self::COVERAGE_MINI_GEAR => 'licence.coverage.mini_gear',
        self::COVERAGE_SMALL_GEAR => 'licence.coverage.small_gear',
        self::COVERAGE_HIGH_GEAR => 'licence.coverage.high_gear',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $number;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $coverage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $magazineSubscription = false;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $subscriptionAmount;

    /**
     * @ORM\Column(type="boolean")
     */
    private $valid = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $additionalFamilyMember = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $medicalCertificateRequired = false;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="licence", cascade={"persist", "remove"})
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;

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

    public function getCoverage(): ?int
    {
        return $this->coverage;
    }

    public function setCoverage(int $coverage): self
    {
        $this->coverage = $coverage;

        return $this;
    }

    public function getMagazineSubscription(): ?bool
    {
        return $this->magazineSubscription;
    }

    public function setMagazineSubscription(bool $magazineSubscription): self
    {
        $this->magazineSubscription = $magazineSubscription;

        return $this;
    }

    public function getSubscriptionAmount(): ?float
    {
        return $this->subscriptionAmount;
    }

    public function setSubscriptionAmount(float $subscriptionAmount): self
    {
        $this->subscriptionAmount = $subscriptionAmount;

        return $this;
    }

    public function getValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getAdditionalFamilyMember(): ?bool
    {
        return $this->additionalFamilyMember;
    }

    public function setAdditionalFamilyMember(bool $additionalFamilyMember): self
    {
        $this->additionalFamilyMember = $additionalFamilyMember;

        return $this;
    }

    public function getMedicalCertificateRequired(): ?bool
    {
        return $this->medicalCertificateRequired;
    }

    public function setMedicalCertificateRequired(bool $medicalCertificateRequired): self
    {
        $this->medicalCertificateRequired = $medicalCertificateRequired;

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
}
