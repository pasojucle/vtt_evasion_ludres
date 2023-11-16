<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\ManyToMany;
use App\Repository\LicenceRepository;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[Entity(repositoryClass: LicenceRepository::class)]
class Licence
{
    public const COVERAGE_MINI_GEAR = 1;

    public const COVERAGE_SMALL_GEAR = 2;

    public const COVERAGE_HIGH_GEAR = 3;

    public const COVERAGES = [
        self::COVERAGE_MINI_GEAR => 'licence.coverage.mini_gear',
        self::COVERAGE_SMALL_GEAR => 'licence.coverage.small_gear',
        self::COVERAGE_HIGH_GEAR => 'licence.coverage.high_gear',
    ];

    public const CATEGORY_MINOR = 1;

    public const CATEGORY_ADULT = 2;

    public const CATEGORY_BOTH = 3;

    public const CATEGORIES = [
        self::CATEGORY_MINOR => 'licence.category.minor',
        self::CATEGORY_ADULT => 'licence.category.adult',
    ];

    public const STATUS_NONE = 0;

    public const STATUS_WAITING_RENEW = 1;

    public const STATUS_IN_PROCESSING = 2;

    public const STATUS_WAITING_VALIDATE = 3;

    public const STATUS_TESTING = 4;

    public const STATUS_VALID = 5;

    public const STATUS_TESTING_IN_PROGRESS = 6;

    public const STATUS_TESTING_COMPLETE = 7;

    public const STATUS_NEW = 8;

    public const STATUS_RENEW = 9;

    public const ALL_USERS = 99;

    public const STATUS = [
        self::STATUS_NONE => 'licence.status.none',
        self::STATUS_WAITING_RENEW => 'licence.status.waiting_renew',
        self::STATUS_IN_PROCESSING => 'licence.status.in_processing',
        self::STATUS_WAITING_VALIDATE => 'licence.status.waiting_validate',
        self::STATUS_TESTING => 'licence.status.testing',
        self::STATUS_VALID => 'licence.status.valid',
    ];

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $coverage = null;

    #[Column(type: 'boolean')]
    private bool $magazineSubscription = false;

    #[Column(type: 'float', nullable: true)]
    private ?float $subscriptionAmount = null;

    #[Column(type: 'boolean')]
    private bool $additionalFamilyMember = false;

    #[Column(type: 'boolean')]
    private bool $medicalCertificateRequired = false;

    #[Column(type: 'integer')]
    private int $category = self::CATEGORY_ADULT;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'licences')]
    private User $user;

    #[Column(type: 'integer')]
    private int $season;

    #[Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $createdAt = null;

    #[Column(type: 'boolean', options:['default' => true])]
    private bool $final = false;

    #[Column(type: 'integer')]
    private int $status = self::STATUS_IN_PROCESSING;

    #[Column(type: 'boolean', options:['default' => false])]
    private bool $currentSeasonForm = false;

    #[Column(type: 'boolean', options:['default' => false])]
    private $isVae = false;

    #[OneToMany(mappedBy: 'licence', targetEntity: LicenceSwornCertification::class)]
    private Collection $licenceSwornCertifications;

    public function __construct()
    {
        $this->licenceSwornCertifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): self
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCurrentSeasonForm(): ?bool
    {
        return $this->currentSeasonForm;
    }

    public function setCurrentSeasonForm(bool $currentSeasonForm): self
    {
        $this->currentSeasonForm = $currentSeasonForm;

        return $this;
    }

    public function isVae(): ?bool
    {
        return $this->isVae;
    }

    public function setIsVae(bool $isVae): self
    {
        $this->isVae = $isVae;

        return $this;
    }

    /**
     * @return Collection<int, LicenceSwornCertification>
     */
    public function getLicenceSwornCertifications(): Collection
    {
        return $this->licenceSwornCertifications;
    }

    public function addLicenceSwornCertification(LicenceSwornCertification $licenceSwornCertification): static
    {
        if (!$this->licenceSwornCertifications->contains($licenceSwornCertification)) {
            $this->licenceSwornCertifications->add($licenceSwornCertification);
            $licenceSwornCertification->setLicence($this);
        }

        return $this;
    }

    public function removeLicenceSwornCertification(LicenceSwornCertification $licenceSwornCertification): static
    {
        if ($this->licenceSwornCertifications->removeElement($licenceSwornCertification)) {
            // set the owning side to null (unless already changed)
            if ($licenceSwornCertification->getLicence() === $this) {
                $licenceSwornCertification->setLicence(null);
            }
        }

        return $this;
    }
}
