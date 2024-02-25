<?php

namespace App\Entity;

use App\Repository\SlideshowImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SlideshowImageRepository::class)]
class SlideshowImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\ManyToOne(inversedBy: 'slideshowImages')]
    private ?SlideshowDirectory $directory = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getDirectory(): ?SlideshowDirectory
    {
        return $this->directory;
    }

    public function setDirectory(?SlideshowDirectory $directory): static
    {
        $this->directory = $directory;

        return $this;
    }
}
