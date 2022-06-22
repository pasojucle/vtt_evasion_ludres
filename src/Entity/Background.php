<?php

namespace App\Entity;

use App\Repository\BackgroundRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BackgroundRepository::class)]
class Background
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $filename;

    #[ORM\ManyToMany(targetEntity: Content::class, mappedBy: 'backgrounds')]
    private Collection $contents;

    #[ORM\Column(type: 'json')]
    private array $landscapePosition = [];

    #[ORM\Column(type: 'json')]
    private array $squarePosition = [];

    public function __construct()
    {
        $this->contents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return Collection<int, Content>
     */
    public function getContents(): Collection
    {
        return $this->contents;
    }

    public function addContent(Content $content): self
    {
        if (!$this->contents->contains($content)) {
            $this->contents[] = $content;
            $content->addBackground($this);
        }

        return $this;
    }

    public function removeContent(Content $content): self
    {
        if ($this->contents->removeElement($content)) {
            $content->removeBackground($this);
        }

        return $this;
    }

    public function getLandscapePosition(): ?array
    {
        return $this->landscapePosition;
    }

    public function setLandscapePosition(array $landscapePosition): self
    {
        $this->landscapePosition = $landscapePosition;

        return $this;
    }

    public function getSquarePosition(): ?array
    {
        return $this->squarePosition;
    }

    public function setSquarePosition(array $squarePosition): self
    {
        $this->squarePosition = $squarePosition;

        return $this;
    }
}
