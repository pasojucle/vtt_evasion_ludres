<?php

namespace App\Entity;

use App\Repository\ParameterRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ParameterRepository::class)
 */
class Parameter
{
    public const TYPE_TEXT = 1;
    public const TYPE_INTEGER = 2;
    public const TYPE_BOOL = 3;
    public const TYPE_ARRAY = 4;

    public const TYPES = [
        self::TYPE_TEXT => 'parameter.type.text',
        self::TYPE_INTEGER => 'parameter.type.interger',
        self::TYPE_BOOL => 'parameter.type.bool',
        self::TYPE_ARRAY => 'parameter.type.array',
    ];
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $label;

    /**
     * @ORM\Column(type="integer")
     */
    private $type = self::TYPE_TEXT;

    /**
     * @ORM\Column(type="text")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity=ParameterGroup::class, inversedBy="parameters")
     * @ORM\JoinColumn(nullable=false)
     */
    private $parameterGroup;

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

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
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
