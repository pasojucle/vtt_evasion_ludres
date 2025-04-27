<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Dto\ActionDto;
use App\Repository\MessageRepository;
use App\State\MessageStateProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(shortName: 'Message')]
#[GetCollection(
    name: 'message_collection',
    output: ActionDto::class,
    provider: MessageStateProvider::class,
)]
#[ApiFilter(SearchFilter::class, properties: ['section.name' => 'exact'])]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $name = '';

    #[ORM\Column(length: 150)]
    private string $label = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $content = '';

    #[ORM\Column(nullable: true)]
    private ?int $levelType = null;

    #[ORM\ManyToOne]
    private ?ParameterGroup $section = null;

    #[ORM\Column(type: Types::BOOLEAN, options:['default' => false])]
    private bool $protected = false;

    public function __toString(): string
    {
        return $this->label;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getLevelType(): ?int
    {
        return $this->levelType;
    }

    public function setLevelType(?int $levelType): static
    {
        $this->levelType = $levelType;

        return $this;
    }

    public function getSection(): ?ParameterGroup
    {
        return $this->section;
    }

    public function setSection(?ParameterGroup $section): static
    {
        $this->section = $section;

        return $this;
    }

    public function isProtected(): bool
    {
        return $this->protected;
    }

    public function setProtected(bool $protected): static
    {
        $this->protected = $protected;

        return $this;
    }
}
