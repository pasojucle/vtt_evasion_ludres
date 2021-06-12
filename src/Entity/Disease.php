<?php

namespace App\Entity;

use App\Repository\DiseaseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DiseaseRepository::class)
 */
class Disease
{
    public const TYPE_DISEASE = 1;
    public const TYPE_ALLERGY = 2;
    public const TYPE_INTOLERANCE = 3;

    public const TYPES = [
        self::TYPE_DISEASE => 'disease.type.diseases',
        self::TYPE_ALLERGY => 'disease.type.allergies',
        self::TYPE_INTOLERANCE => 'disease.type.intolerances',
    ];

    private const LABEL_ENURESIS = 1;
    private const LABEL_TETANY = 2;
    private const LABEL_ASTHMA = 3;
    private const LABEL_HEMOPHILIA = 4;
    private const LABEL_EPILEPSY = 5;
    private const LABEL_DIABETES = 6;
    public const LABEL_OTHER = 7;
    private const LABEL_DIETARY = 8;
    private const LABEL_MEDICATED = 9;
    public const LABEL_POLLEN_BEES = 10;
    private const LABEL_FOOD = 11;
    private const LABEL_MEDICINES = 12;

    public const LABELS = [
        self::LABEL_ENURESIS => 'disease.label.enuresis',
        self::LABEL_TETANY => 'disease.label.tetany',
        self::LABEL_ASTHMA => 'disease.label.asthma',
        self::LABEL_HEMOPHILIA => 'disease.label.hemophilia',
        self::LABEL_EPILEPSY => 'disease.label.epilepsy',
        self::LABEL_DIABETES => 'disease.label.diabetes',
        self::LABEL_OTHER => 'disease.label.other',
        self::LABEL_DIETARY => 'disease.label.dietary',
        self::LABEL_MEDICATED => 'disease.label.medicated',
        self::LABEL_POLLEN_BEES => 'disease.label.pollen_bees',
        self::LABEL_FOOD => 'disease.label.food',
        self::LABEL_MEDICINES => 'disease.label.medicines',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $curentTreatment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $emergencyTreatment;


    /**
     * @ORM\Column(type="integer")
     */
    private $label;

    /**
     * @ORM\ManyToOne(targetEntity=Health::class, inversedBy="diseases")
     * @ORM\JoinColumn(nullable=false)
     */
    private $health;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLabel(): ?int
    {
        return $this->label;
    }

    public function setLabel(int $label): self
    {
        $this->label = $label;

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
