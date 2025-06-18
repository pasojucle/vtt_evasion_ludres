<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Entity\Enum\ParameterKindEnum;
use App\Repository\ParameterRepository;
use App\State\ParameterStateProcessor;

#[ORM\Entity(repositoryClass: ParameterRepository::class)]
#[ApiResource(
    shortName: 'Parameter',
    security: "is_granted('ROLE_USER')",
)]
#[GetCollection()]
#[Patch(
    processor: ParameterStateProcessor::class
)]

class Parameter
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "NONE")]
    #[ORM\Column(length: 50)]
    #[ApiProperty(identifier: true)]
    private string $name = 'UNDEFINED';

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $value = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $options = null;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    #[ORM\Column(type: 'ParameterKind')]
    private ParameterKindEnum $kind = ParameterKindEnum::TEXT;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getKind(): object
    {
        return $this->kind;
    }

    public function setKind(ParameterKindEnum $kind): static
    {
        $this->kind = $kind;

        return $this;
    }
}
