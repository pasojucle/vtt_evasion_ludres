<?php

namespace App\Entity;

use App\Entity\Enum\AgreementKindEnum;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceMembershipEnum;
use App\Repository\AgreementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AgreementRepository::class)]
class Agreement
{
    #[ORM\Id]
    #[ORM\Column(length: 25, unique: true)]
    private ?string $id = null;

    #[ORM\Column(length: 50)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: 'LicenceCategory')]
    private LicenceCategoryEnum $category = LicenceCategoryEnum::ADULT;

    #[ORM\Column(type: 'LicenceMembership')]
    private LicenceMembershipEnum $membership = LicenceMembershipEnum::TRIAL;

    #[ORM\Column(type: 'AgreementKind')]
    private ?AgreementKindEnum $kind = AgreementKindEnum::AUTHORIZATION;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $authorizationMessage = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $rejectionMessage = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $authorizationIcon = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $rejectionIcon = null;

    /**
     * @var Collection<int, RegistrationStep>
     */
    #[ORM\ManyToMany(targetEntity: RegistrationStep::class, mappedBy: 'agreements')]
    private Collection $registrationSteps;

    /**
     * @var Collection<int, LicenceAgreement>
     */
    #[ORM\OneToMany(targetEntity: LicenceAgreement::class, mappedBy: 'agreement')]
    private Collection $licenceAgreements;

    #[ORM\Column(nullable: true)]
    private ?int $orderBy = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $enabled = true;

    public function __construct()
    {
        $this->registrationSteps = new ArrayCollection();
        $this->licenceAgreements = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    public function getMembership(): LicenceMembershipEnum
    {
        return $this->membership;
    }

    public function setMembership(LicenceMembershipEnum $membership): static
    {
        $this->membership = $membership;

        return $this;
    }

    public function getKind(): AgreementKindEnum
    {
        return $this->kind;
    }

    public function setKind(AgreementKindEnum $kind): static
    {
        $this->kind = $kind;

        return $this;
    }

    public function getAuthorizationMessage(): ?string
    {
        return $this->authorizationMessage;
    }

    public function setAuthorizationMessage(?string $authorizationMessage): static
    {
        $this->authorizationMessage = $authorizationMessage;

        return $this;
    }

    public function getRejectionMessage(): ?string
    {
        return $this->rejectionMessage;
    }

    public function setRejectionMessage(?string $rejectionMessage): static
    {
        $this->rejectionMessage = $rejectionMessage;

        return $this;
    }

    public function getAuthorizationIcon(): ?string
    {
        return $this->authorizationIcon;
    }

    public function setAuthorizationIcon(?string $authorizationIcon): static
    {
        $this->authorizationIcon = $authorizationIcon;

        return $this;
    }

    public function getRejectionIcon(): ?string
    {
        return $this->rejectionIcon;
    }

    public function setRejectionIcon(?string $rejectionIcon): static
    {
        $this->rejectionIcon = $rejectionIcon;

        return $this;
    }

    /**
     * @return Collection<int, RegistrationStep>
     */
    public function getRegistrationSteps(): Collection
    {
        return $this->registrationSteps;
    }

    public function addRegistrationStep(RegistrationStep $registrationStep): static
    {
        if (!$this->registrationSteps->contains($registrationStep)) {
            $this->registrationSteps->add($registrationStep);
            $registrationStep->addAgreement($this);
        }

        return $this;
    }

    public function removeRegistrationStep(RegistrationStep $registrationStep): static
    {
        if ($this->registrationSteps->removeElement($registrationStep)) {
            $registrationStep->removeAgreement($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, LicenceAgreement>
     */
    public function getLicenceAgreements(): Collection
    {
        return $this->licenceAgreements;
    }

    public function addLicenceAgreement(LicenceAgreement $licenceAgreement): static
    {
        if (!$this->licenceAgreements->contains($licenceAgreement)) {
            $this->licenceAgreements->add($licenceAgreement);
            $licenceAgreement->setAgreement($this);
        }

        return $this;
    }

    public function removeLicenceAgreement(LicenceAgreement $licenceAgreement): static
    {
        if ($this->licenceAgreements->removeElement($licenceAgreement)) {
            // set the owning side to null (unless already changed)
            if ($licenceAgreement->getAgreement() === $this) {
                $licenceAgreement->setAgreement(null);
            }
        }

        return $this;
    }

    public function getOrderBy(): ?int
    {
        return $this->orderBy;
    }

    public function setOrderBy(?int $orderBy): static
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
}
