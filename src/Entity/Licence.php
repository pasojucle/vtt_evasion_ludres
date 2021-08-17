<?php

namespace App\Entity;

use App\Repository\LicenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LicenceRepository::class)
 */
class Licence
{
    public const TYPE_RIDE = 1;
    public const TYPE_HIKE = 2;
    public const TYPE_SPORT = 3;

    public const TYPES = [
        self::TYPE_RIDE => 'licence.type.ride',
        self::TYPE_HIKE => 'licence.type.hike',
        self::TYPE_SPORT => 'licence.type.sport',
    ];

    public const COVERAGE_MINI_GEAR = 1;
    public const COVERAGE_SMALL_GEAR  = 2;
    public const COVERAGE_HIGH_GEAR  = 3;

    public const COVERAGES = [
        self::COVERAGE_MINI_GEAR => 'licence.coverage.mini_gear',
        self::COVERAGE_SMALL_GEAR => 'licence.coverage.small_gear',
        self::COVERAGE_HIGH_GEAR => 'licence.coverage.high_gear',
    ];

    public const CATEGORY_MINOR = 1;
    public const CATEGORY_ADULT  = 2;

    public const CATEGORIES = [
        self::CATEGORY_MINOR => 'licence.category.minor',
        self::CATEGORY_ADULT => 'licence.category.adult',
    ];

    public const STATUS_NONE = 2;
    public const STATUS_IN_PROGRESS  = 0;
    public const STATUS_VALID  = 1;

    public const STATUS = [
        self::STATUS_NONE => 'licence.status.none',
        self::STATUS_IN_PROGRESS => 'licence.status.in_progress',
        self::STATUS_VALID => 'licence.status.valid',
    ];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

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
     * @ORM\Column(type="integer")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="licences")
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $season;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean", options={"default":true})
     */
    private $final = false;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isDownload = 0;
    
    public function getId(): ?int
    {
        return $this->id;
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

    public function isValid(): ?bool
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

    public function isMedicalCertificateRequired(): ?bool
    {
        return $this->medicalCertificateRequired;
    }

    public function setMedicalCertificateRequired(bool $medicalCertificateRequired): self
    {
        $this->medicalCertificateRequired = $medicalCertificateRequired;

        return $this;
    }

    public function getCategory(): ?int
    {
        return $this->category;
    }

    public function setCategory(int $category): self
    {
        $this->category = $category;

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

    public function getSeason(): ?int
    {
        return $this->season;
    }

    public function setSeason(int $season): self
    {
        $this->season = $season;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isFinal(): ?bool
    {
        return $this->final;
    }

    public function setFinal(bool $final): self
    {
        $this->final = $final;

        return $this;
    }

    public function isDownload(): ?bool
    {
        return $this->isDownload;
    }

    public function setIsDownload(bool $isDownload): self
    {
        $this->isDownload = $isDownload;

        return $this;
    }
}
