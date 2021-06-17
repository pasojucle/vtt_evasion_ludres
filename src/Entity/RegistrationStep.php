<?php

namespace App\Entity;

use App\Repository\RegistrationStepRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass=RegistrationStepRepository::class)
 * @ORM\EntityListeners({"App\EventListeners\EntityListener"})
 */
class RegistrationStep
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $filename;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $form;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderBy;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    private $class;

    private $file;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $category;

    /**
     * @ORM\Column(type="boolean")
     */
    private $testing;

    /**
     * @ORM\OneToMany(targetEntity=RegistrationStepContent::class, mappedBy="registrationStep")
     */
    private $contents;

    /**
     * @ORM\Column(type="boolean")
     */
    private $toPdf;


    public function __construct()
    {
        $this->types = new ArrayCollection();
        $this->contents = new ArrayCollection();
    }


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

    public function getTesting(): ?bool
    {
        return $this->testing;
    }

    public function setTesting(bool $testing): self
    {
        $this->testing = $testing;

        return $this;
    }

    /**
     * @return Collection|RegistrationStepContent[]
     */
    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function addContent(RegistrationStepContent $content): self
    {
        if (!$this->contents->contains($content)) {
            $this->contents[] = $content;
            $content->setRegistrationStep($this);
        }

        return $this;
    }

    public function removeContent(RegistrationStepContent $content): self
    {
        if ($this->contents->removeElement($content)) {
            // set the owning side to null (unless already changed)
            if ($content->getRegistrationStep() === $this) {
                $content->setRegistrationStep(null);
            }
        }

        return $this;
    }

    public function isToPdf(): ?bool
    {
        return $this->toPdf;
    }

    public function setToPdf(bool $toPdf): self
    {
        $this->toPdf = $toPdf;

        return $this;
    }
}
