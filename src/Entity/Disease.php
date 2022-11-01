<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DiseaseRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: DiseaseRepository::class)]
class Disease
{
    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 100, nullable: true)]
    private ?string $title = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $curentTreatment = null;

    #[Column(type: 'text', nullable: true)]
    private ?string $emergencyTreatment = null;

    #[ManyToOne(targetEntity: Health::class, inversedBy: 'diseases')]
    #[JoinColumn(nullable: false)]
    private Health $health;

    #[ManyToOne(inversedBy: 'diseases')]
    private ?DiseaseKind $diseaseKind = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCurentTreatment(): ?string
    {
        return $this->curentTreatment;
    }

    public function setCurentTreatment(?string $curentTreatment): self
    {
        $this->curentTreatment = $curentTreatment;

        return $this;
    }

    public function getEmergencyTreatment(): ?string
    {
        return $this->emergencyTreatment;
    }

    public function setEmergencyTreatment(?string $emergencyTreatment): self
    {
        $this->emergencyTreatment = $emergencyTreatment;

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

    public function getDiseaseKind(): ?DiseaseKind
    {
        return $this->diseaseKind;
    }

    public function setDiseaseKind(?DiseaseKind $diseaseKind): self
    {
        $this->diseaseKind = $diseaseKind;

        return $this;
    }
}
