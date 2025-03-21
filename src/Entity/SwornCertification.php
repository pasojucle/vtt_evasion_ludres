<?php

namespace App\Entity;

use App\Repository\SwornCertificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SwornCertificationRepository::class)]
class SwornCertification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $label = null;

    #[ORM\Column(type:Types::BOOLEAN, options:['default' => false])]
    private bool $school = false;

    #[ORM\Column(type:Types::BOOLEAN, options:['default' => false])]
    private bool $adult = false;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function isSchool(): ?bool
    {
        return $this->school;
    }

    public function setSchool(bool $school): static
    {
        $this->school = $school;

        return $this;
    }

    public function isAdult(): ?bool
    {
        return $this->adult;
    }

    public function setAdult(bool $adult): static
    {
        $this->adult = $adult;

        return $this;
    }
}
