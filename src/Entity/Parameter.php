<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ParameterRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: ParameterRepository::class)]
class Parameter
{
    public const TYPE_TEXT = 1;

    public const TYPE_INTEGER = 2;

    public const TYPE_BOOL = 3;

    public const TYPE_ARRAY = 4;

    public const TYPE_MONTH_AND_DAY = 5;
    
    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 100)]
    private string $name;

    #[Column(type: 'string', length: 150)]
    private string $label;

    #[Column(type: 'integer')]
    private int $type = self::TYPE_TEXT;

    #[Column(type: 'text')]
    private string $value;

    #[ManyToOne(targetEntity: ParameterGroup::class, inversedBy: 'parameters')]
    #[JoinColumn(nullable: false)]
    private ParameterGroup $parameterGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): string|bool|array|null
    {
        if (null === $this->value) {
            return $this->value;
        }
        
        return match ($this->type) {
            self::TYPE_BOOL =>(bool) $this->value,
            self::TYPE_INTEGER => (int) $this->value,
            self::TYPE_ARRAY, self::TYPE_MONTH_AND_DAY => json_decode($this->value, true),
            default => $this->value
        };
    }

    public function setValue(string|array $value): self
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $this->value = $value;

        return $this;
    }

    public function getParameterGroup(): ?ParameterGroup
    {
        return $this->parameterGroup;
    }

    public function setParameterGroup(?ParameterGroup $parameterGroup): self
    {
        $this->parameterGroup = $parameterGroup;

        return $this;
    }
}
