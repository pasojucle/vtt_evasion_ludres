<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Enum\DisplayModeEnum;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\RegistrationFormEnum;
use Doctrine\Common\Collections\Collection;
use App\Repository\RegistrationStepRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: RegistrationStepRepository::class)]
class RegistrationStep
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $filename;

    #[ORM\Column(type: 'RegistrationForm')]
    private RegistrationFormEnum $form = RegistrationFormEnum::NONE;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $orderBy = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content;

    private ?string $class;

    private UploadedFile $file;

    #[ORM\ManyToOne(targetEntity: RegistrationStepGroup::class, inversedBy: 'registrationSteps')]
    private RegistrationStepGroup $registrationStepGroup;

    #[ORM\Column(type:Types::BOOLEAN, options:['default' => false])]
    private bool $personal = false;

    #[ORM\Column(type: 'LicenceCategory', options:['default' => LicenceCategoryEnum::SCHOOL_AND_ADULT->value])]
    private LicenceCategoryEnum $category = LicenceCategoryEnum::SCHOOL_AND_ADULT;

    #[ORM\Column(type: 'DisplayMode')]
    private DisplayModeEnum $trialDisplayMode = DisplayModeEnum::NONE;

    #[ORM\Column(type: 'DisplayMode')]
    private DisplayModeEnum $yearlyDisplayMode = DisplayModeEnum::NONE;

    /**
     * @var Collection<int, Agreement>
     */
    #[ORM\ManyToMany(targetEntity: Agreement::class, inversedBy: 'registrationSteps')]
    private Collection $agreements;

    public function __construct()
    {
        $this->agreements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getForm(): RegistrationFormEnum
    {
        return $this->form;
    }

    public function setForm(RegistrationFormEnum $form): static
    {
        $this->form = $form;

        return $this;
    }

    public function getOrderBy(): ?int
    {
        return $this->orderBy;
    }

    public function setOrderBy(int $orderBy): static
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(?string $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getRegistrationStepGroup(): ?RegistrationStepGroup
    {
        return $this->registrationStepGroup;
    }

    public function setRegistrationStepGroup(?RegistrationStepGroup $registrationStepGroup): static
    {
        $this->registrationStepGroup = $registrationStepGroup;

        return $this;
    }

    public function isPersonal(): bool
    {
        return $this->personal;
    }

    public function setPersonal(bool $personal): static
    {
        $this->personal = $personal;

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

    public function getTrialDisplayMode(): DisplayModeEnum
    {
        return $this->trialDisplayMode;
    }

    public function setTrialDisplayMode(DisplayModeEnum $trialDisplayMode): static
    {
        $this->trialDisplayMode = $trialDisplayMode;

        return $this;
    }

    public function getYearlyDisplayMode(): DisplayModeEnum
    {
        return $this->yearlyDisplayMode;
    }

    public function setYearlyDisplayMode(DisplayModeEnum $yearlyDisplayMode): static
    {
        $this->yearlyDisplayMode = $yearlyDisplayMode;

        return $this;
    }

    /**
     * @return Collection<int, Agreement>
     */
    public function getAgreements(): Collection
    {
        return $this->agreements;
    }

    public function addAgreement(Agreement $agreement): static
    {
        if (!$this->agreements->contains($agreement)) {
            $this->agreements->add($agreement);
        }

        return $this;
    }

    public function removeAgreement(Agreement $agreement): static
    {
        $this->agreements->removeElement($agreement);

        return $this;
    }
}
