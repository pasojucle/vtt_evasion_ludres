<?php

namespace App\Entity;

use App\Repository\HealthQuestionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=HealthQuestionRepository::class)
 */
class HealthQuestion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */ 
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $field;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity=Health::class, inversedBy="healthQuestions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $health;

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
