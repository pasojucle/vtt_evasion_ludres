<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\HealthQuestionRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: HealthQuestionRepository::class)]
class HealthQuestion
{
    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'integer')]
    private int $field;

    #[Column(type: 'boolean', nullable: true)]
    private ?bool $value = null;

    #[ManyToOne(targetEntity: Health::class, inversedBy: 'healthQuestions')]
    private ?Health $health;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getField(): ?int
    {
        return $this->field;
    }

    public function setField(int $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getValue(): ?bool
    {
        return $this->value;
    }

    public function setValue(bool $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getHealth(): ?Health
    {
        return $this->health;
    }

    public function setHealth(?Health $health): self
    {
        $this->health = $health;

        return $this;
    }
}
