<?php

namespace App\Entity;

use App\Entity\Enum\PracticeEnum;
use App\Repository\PublicRegistrationRateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicRegistrationRateRepository::class)]
class PublicRegistrationRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: PracticeEnum::class, options: ['default' => PracticeEnum::VTT->value])]
    private PracticeEnum $practice = PracticeEnum::VTT;

    #[ORM\Column]
    private ?int $maxAge = null;

    #[ORM\Column]
    private ?int $amount = null;

    #[ORM\Column(nullable: true)]
    private ?bool $FFVelo = null;

    #[ORM\Column(length: 255)]
    private string $label = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPractice(): PracticeEnum
    {
        return $this->practice;
    }

    public function setPractice(PracticeEnum $practice): static
    {
        $this->practice = $practice;

        return $this;
    }

    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    public function setMaxAge(int $maxAge): static
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function isFFVelo(): ?bool
    {
        return $this->FFVelo;
    }

    public function setFFVelo(?bool $FFVelo): static
    {
        $this->FFVelo = $FFVelo;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }
}
