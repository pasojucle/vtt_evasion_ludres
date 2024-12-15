<?php

namespace App\Entity;

use App\Repository\SecondHandImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SecondHandImageRepository::class)]
class SecondHandImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SecondHand $secondHand = null;

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

    public function getSecondHand(): ?SecondHand
    {
        return $this->secondHand;
    }

    public function setSecondHand(?SecondHand $secondHand): static
    {
        $this->secondHand = $secondHand;

        return $this;
    }
}
