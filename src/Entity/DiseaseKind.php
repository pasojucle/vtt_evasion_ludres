<?php

namespace App\Entity;

use App\Repository\DiseaseKindRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiseaseKindRepository::class)]
class DiseaseKind
{
    public const CATEGORY_DISEASE = 1;
    public const CATEGORY_ALLERGY = 2;
    public const CATEGORY_INTOLERANCE = 3;
    public const CATEGORIES = [
        self::CATEGORY_DISEASE => 'disease.type.diseases',
        self::CATEGORY_ALLERGY => 'disease.type.allergies',
        self::CATEGORY_INTOLERANCE => 'disease.type.intolerances',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';

    #[ORM\Column(type: 'integer')]
    private int $category = 1;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $bikeRideAlert = false;

    #[ORM\OneToMany(mappedBy: 'diseaseKind', targetEntity: Disease::class)]
    private Collection $diseases;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $customLabel = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $emergencyTreatment = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $deleted = false;

    #[ORM\Column(type: 'integer')]
    private int $orderBy = -1;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $licenceCategory = null;

    public function __construct()
    {
        $this->diseases = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): int
    {
        return $this->category;
    }

    public function setCategory(int $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function hasBikeRideAlert(): bool
    {
        return $this->bikeRideAlert;
    }

    public function setBikeRideAlert(bool $bikeRideAlert): self
    {
        $this->bikeRideAlert = $bikeRideAlert;

        return $this;
    }

    /**
     * @return Collection<int, Disease>
     */
    public function getDiseases(): Collection
    {
        return $this->diseases;
    }

    public function addDisease(Disease $disease): self
    {
        if (!$this->diseases->contains($disease)) {
            $this->diseases->add($disease);
            $disease->setDiseaseKind($this);
        }

        return $this;
    }

    public function removeDisease(Disease $disease): self
    {
        if ($this->diseases->removeElement($disease)) {
            // set the owning side to null (unless already changed)
            if ($disease->getDiseaseKind() === $this) {
                $disease->setDiseaseKind(null);
            }
        }

        return $this;
    }

    public function hasCustomLabel(): bool
    {
        return $this->customLabel;
    }

    public function setCustomLabel(bool $customLabel): self
    {
        $this->customLabel = $customLabel;

        return $this;
    }

    public function hasEmergencyTreatment(): bool
    {
        return $this->emergencyTreatment;
    }

    public function setEmergencyTreatment(bool $emergencyTreatment): self
    {
        $this->emergencyTreatment = $emergencyTreatment;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getOrderBy(): int
    {
        return $this->orderBy;
    }

    public function setOrderBy(int $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    public function getLicenceCategory(): ?int
    {
        return $this->licenceCategory;
    }

    public function setLicenceCategory(?int $licenceCategory): self
    {
        $this->licenceCategory = $licenceCategory;

        return $this;
    }
}
