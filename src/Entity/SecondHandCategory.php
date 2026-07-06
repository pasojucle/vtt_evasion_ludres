<?php

namespace App\Entity;

use App\Entity\Interface\SoftDeletableInterface;
use App\Entity\Trait\SoftDeletableTrait;
use App\Repository\SecondHandCategoryRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: SecondHandCategoryRepository::class)]
class SecondHandCategory implements SoftDeletableInterface
{
    use SoftDeletableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 30)]
    private string $icon = 'lucide:a-arrow-down';

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }
}
