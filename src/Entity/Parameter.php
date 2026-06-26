<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ParameterRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: ParameterRepository::class)]
class Parameter
{
    public const TYPE_HTML = 1;

    public const TYPE_INTEGER = 2;

    public const TYPE_BOOL = 3;

    public const TYPE_ARRAY = 4;

    public const TYPE_MONTH_AND_DAY = 5;

    public const TYPE_TEXT = 6;

    #[Column(type: 'string', length: 100)]
    #[Id]
    private string $id;

    #[Column(type: 'string', length: 150)]
    private string $label;

    #[Column(type: 'integer')]
    private int $type = self::TYPE_HTML;

    #[Column(type: 'text')]
    private string $value;

    #[ManyToOne(targetEntity: Section::class, inversedBy: 'parameters')]
    #[JoinColumn(nullable: false)]
    private Section $section;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

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

    public function getValue(): string|bool|array|int|null
    {
        return match ($this->type) {
            self::TYPE_BOOL => (bool) $this->value,
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

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): self
    {
        $this->section = $section;

        return $this;
    }
}
