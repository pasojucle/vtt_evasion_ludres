<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\LicenceStateEnum;
use App\Repository\LicenceRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LicenceRepository::class)]
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

    public const FILTER_NONE = 0;

    public const FILTER_WAITING_RENEW = 1;

    public const FILTER_IN_PROCESSING = 2;

    public const FILTER_WAITING_VALIDATE = 3;

    public const FILTER_TESTING = 4;

    public const FILTER_VALID = 5;

    public const FILTER_TESTING_IN_PROGRESS = 6;

    public const FILTER_TESTING_COMPLETE = 7;

    public const FILTER_NEW = 8;

    public const FILTER_RENEW = 9;

    public const FILTER_TO_REGISTER = 10;

    public const ALL_USERS = 99;

    public const FILTERS = [
        self::FILTER_NONE => 'licence.filter.none',
        self::FILTER_WAITING_RENEW => 'licence.filter.waiting_renew',
        self::FILTER_IN_PROCESSING => 'licence.filter.in_processing',
        self::FILTER_WAITING_VALIDATE => 'licence.filter.waiting_validate',
        self::FILTER_TESTING => 'licence.filter.testing',
        self::FILTER_VALID => 'licence.filter.valid',
        self::FILTER_TO_REGISTER => 'licence.filter.to_register',
    ];

    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $coverage = null;

    #[ORM\Column(type: 'boolean')]
    private bool $magazineSubscription = false;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $subscriptionAmount = null;

    #[ORM\Column(type: 'boolean')]
    private bool $additionalFamilyMember = false;

    #[ORM\Column(type: 'boolean')]
    private bool $medicalCertificateRequired = false;

    #[ORM\Column(type: 'integer')]
    private int $category = self::CATEGORY_ADULT;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'licences')]
    private User $user;

    #[ORM\Column(type: 'integer')]
    private int $season;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'boolean', options:['default' => false])]
    private bool $currentSeasonForm = false;

    #[ORM\Column(type: 'boolean', options:['default' => false])]
    private $isVae = false;

    #[ORM\OneToMany(mappedBy: 'licence', targetEntity: LicenceSwornCertification::class)]
    private Collection $licenceSwornCertifications;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $testingAt = null;

    #[ORM\Column(type: 'LicenceState', options:['default' => LicenceStateEnum::DRAFT->value])]
    private LicenceStateEnum $state = LicenceStateEnum::DRAFT;

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

    public function getTestingAt(): ?DateTimeImmutable
    {
        return $this->testingAt;
    }

    public function setTestingAt(?DateTimeImmutable $testingAt): static
    {
        $this->testingAt = $testingAt;

        return $this;
    }

    public function getState(): ?object
    {
        return $this->state;
    }

    public function setState(object $state): static
    {
        $this->state = $state;

        return $this;
    }
}
