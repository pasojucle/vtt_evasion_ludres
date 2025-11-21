<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\LicenceCategoryEnum;
use App\Repository\RegistrationStepRepository;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: RegistrationStepRepository::class)]
class RegistrationStep
{
    public const RENDER_NONE = 0;

    public const RENDER_VIEW = 1;

    public const RENDER_FILE = 2;

    public const RENDER_FILE_AND_VIEW = 3;

    public const RENDER_FILE_AND_LINK = 4;

    public const RENDERS = [
        self::RENDER_NONE => 'registration_step.render.none',
        self::RENDER_VIEW => 'registration_step.render.view',
        self::RENDER_FILE => 'registration_step.render.file',
        self::RENDER_FILE_AND_VIEW => 'registration_step.render.file_and_view',
        self::RENDER_FILE_AND_LINK => 'registration_step.render.file_and_link',
    ];

    #[ORM\Column(type: 'integer')]
    #[ORM\Id, ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $filename;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $form;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $orderBy = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content;

    private ?string $class;

    private UploadedFile $file;

    #[ORM\Column(type: 'integer')]
    private int $testingRender;

    #[ORM\ManyToOne(targetEntity: RegistrationStepGroup::class, inversedBy: 'registrationSteps')]
    private RegistrationStepGroup $registrationStepGroup;

    #[ORM\Column(type: 'integer')]
    private int $finalRender;

    #[ORM\Column(type:Types::BOOLEAN, options:['default' => false])]
    private bool $personal = false;

    #[ORM\Column(type: 'LicenceCategory', options:['default' => LicenceCategoryEnum::SCHOOL_AND_ADULT->value])]
    private LicenceCategoryEnum $category = LicenceCategoryEnum::SCHOOL_AND_ADULT;

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

    public function getForm(): ?int
    {
        return $this->form;
    }

    public function setForm(?int $form): static
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

    public function getTestingRender(): ?int
    {
        return $this->testingRender;
    }

    public function setTestingRender(int $testingRender): static
    {
        $this->testingRender = $testingRender;

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

    public function getFinalRender(): ?int
    {
        return $this->finalRender;
    }

    public function setFinalRender(int $finalRender): static
    {
        $this->finalRender = $finalRender;

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

    public function getCategory(): ?object
    {
        return $this->category;
    }

    public function setCategory(object $category): static
    {
        $this->category = $category;

        return $this;
    }
}
