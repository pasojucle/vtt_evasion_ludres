<?php

namespace App\Entity;

use App\Repository\IndemnityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IndemnityRepository::class)]
class Indemnity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Level::class, inversedBy: 'indemnities')]
    #[ORM\JoinColumn(nullable: false)]
    private $level;

    #[ORM\Column(type: 'integer')]
    private $bikeRideType;

    #[ORM\Column(type: 'float')]
    private $amount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getBikeRideType(): ?int
    {
        return $this->bikeRideType;
    }

    public function setBikeRideType(int $bikeRideType): self
    {
        $this->bikeRideType = $bikeRideType;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
