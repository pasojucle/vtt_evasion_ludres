<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RegistrationStepRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Entity(repositoryClass: RegistrationStepRepository::class)]
class RegistrationStep
{
    public const RENDER_NONE = 0;

    public const RENDER_VIEW = 1;

    public const RENDER_FILE = 2;

    public const RENDER_FILE_AND_VIEW = 3;

    public const RENDERS = [
        self::RENDER_NONE => 'registration_step.render.none',
        self::RENDER_VIEW => 'registration_step.render.view',
        self::RENDER_FILE => 'registration_step.render.file',
        self::RENDER_FILE_AND_VIEW => 'registration_step.render.file_and_view',
    ];

    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 255)]
    private string $title;

    #[Column(type: 'string', length: 255, nullable: true)]
    private ?string $filename;

    #[Column(type: 'integer', nullable: true)]
    private ?int $form;

    #[Column(type: 'integer')]
    private int $orderBy;

    #[Column(type: 'text', nullable: true)]
    private ?string $content;

    private ?string $class;

    private UploadedFile $file;

    #[Column(type: 'integer', nullable: true)]
    private ?int $category;

    #[Column(type: 'integer')]
    private int $testingRender;

    #[ManyToOne(targetEntity: RegistrationStepGroup::class, inversedBy: 'registrationSteps')]
    private RegistrationStepGroup $registrationStepGroup;

    #[Column(type: 'integer')]
    private int $finalRender;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getForm(): ?int
    {
        return $this->form;
    }

    public function setForm(?int $form): self
    {
        $this->form = $form;

        return $this;
    }

    public function getOrderBy(): ?int
    {
        return $this->orderBy;
    }

    public function setOrderBy(int $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(?string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getCategory(): ?int
    {
        return $this->category;
    }

    public function setCategory(?int $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getTestingRender(): ?int
    {
        return $this->testingRender;
    }

    public function setTestingRender(int $testingRender): self
    {
        $this->testingRender = $testingRender;

        return $this;
    }

    public function getRegistrationStepGroup(): ?RegistrationStepGroup
    {
        return $this->registrationStepGroup;
    }

    public function setRegistrationStepGroup(?RegistrationStepGroup $registrationStepGroup): self
    {
        $this->registrationStepGroup = $registrationStepGroup;

        return $this;
    }

    public function getFinalRender(): ?int
    {
        return $this->finalRender;
    }

    public function setFinalRender(int $finalRender): self
    {
        $this->finalRender = $finalRender;

        return $this;
    }
}
