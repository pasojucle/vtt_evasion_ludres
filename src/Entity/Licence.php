<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\AgreementKindEnum;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceOptionEnum;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Enum\RegistrationFormEnum;
use App\Repository\LicenceRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\AggregatedType;

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
    private bool $medicalCertificateRequired = false;

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

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $testingAt = null;

    #[ORM\Column(type: 'LicenceState', options:['default' => LicenceStateEnum::DRAFT->value])]
    private LicenceStateEnum $state = LicenceStateEnum::DRAFT;

    #[ORM\Column(type:Types::JSON)]
    private array $options = [LicenceOptionEnum::NO_ADDITIONAL_OPTION];

    #[ORM\Column(type: 'LicenceCategory')]
    private LicenceCategoryEnum $category = LicenceCategoryEnum::ADULT;

    /**
     * @var Collection<int, LicenceAgreement>
     */
    #[ORM\OneToMany(targetEntity: LicenceAgreement::class, mappedBy: 'licence')]
    private Collection $licenceAgreements;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'familyMembers')]
    private ?self $familyMember = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'familyMember')]
    private Collection $familyMembers;

    public function __construct()
    {
        $this->licenceAgreements = new ArrayCollection();
        $this->familyMembers = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getUser()->getLicenceNumber();
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

    public function isMedicalCertificateRequired(): ?bool
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

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getCategory(): LicenceCategoryEnum
    {
        return $this->category;
    }

    public function setCategory(LicenceCategoryEnum $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, LicenceAgreement>
     */
    public function getLicenceAgreements(): Collection
    {
        return $this->licenceAgreements;
    }

    private function getLicenceAgreementsByRegistrationForm(RegistrationFormEnum $form, ?AgreementKindEnum $kind = null): array
    {
        $licenceAgreementsByRegistrationForm = [];
        foreach ($this->licenceAgreements as $licenceAgreement) {
            foreach ($licenceAgreement->getAgreement()->getRegistrationSteps() as $registrationStep) {
                if ($kind && $kind !== $licenceAgreement->getAgreement()->getKind()) {
                    continue;
                }
                if ($form === $registrationStep->getForm()) {
                    $licenceAgreementsByRegistrationForm[$licenceAgreement->getAgreement()->getId()] = $licenceAgreement;
                }
            }
        }

        return $licenceAgreementsByRegistrationForm;
    }

    public function getLicenceAuthorizations(): array
    {
        return $this->getLicenceAgreementsByRegistrationForm(RegistrationFormEnum::LICENCE_AGREEMENTS, AgreementKindEnum::AUTHORIZATION);
    }

    public function getLicenceAuthorizationAgreements(): array
    {
        return $this->getLicenceAgreementsByRegistrationForm(RegistrationFormEnum::LICENCE_AGREEMENTS);
    }

    public function getLicenceHealthAgreements(): array
    {
        return $this->getLicenceAgreementsByRegistrationForm(RegistrationFormEnum::HEALTH_QUESTION);
    }

    public function getLicenceOverviewAgreements(): array
    {
        return $this->getLicenceAgreementsByRegistrationForm(RegistrationFormEnum::OVERVIEW);
    }

    public function addLicenceAgreement(LicenceAgreement $licenceAgreement): static
    {
        if (!$this->licenceAgreements->contains($licenceAgreement)) {
            $this->licenceAgreements->add($licenceAgreement);
            $licenceAgreement->setLicence($this);
        }

        return $this;
    }

    public function removeLicenceAgreement(LicenceAgreement $licenceAgreement): static
    {
        if ($this->licenceAgreements->removeElement($licenceAgreement)) {
            // set the owning side to null (unless already changed)
            if ($licenceAgreement->getLicence() === $this) {
                $licenceAgreement->setLicence(null);
            }
        }

        return $this;
    }

    public function getFamilyMember(): ?self
    {
        return $this->familyMember;
    }

    public function setFamilyMember(?self $familyMember): static
    {
        $this->familyMember = $familyMember;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFamilyMembers(): Collection
    {
        return $this->familyMembers;
    }

    public function addFamilyMember(self $familyMember): static
    {
        if (!$this->familyMembers->contains($familyMember)) {
            $this->familyMembers->add($familyMember);
            $familyMember->setFamilyMember($this);
        }

        return $this;
    }

    public function removeFamilyMember(self $familyMember): static
    {
        if ($this->familyMembers->removeElement($familyMember)) {
            // set the owning side to null (unless already changed)
            if ($familyMember->getFamilyMember() === $this) {
                $familyMember->setFamilyMember(null);
            }
        }

        return $this;
    }
}
