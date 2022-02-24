<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\RegistrationStepGroupRepository;


#[Entity(repositoryClass: RegistrationStepGroupRepository::class)]
class RegistrationStepGroup
{

    #[Column(type: "integer")]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: "string", length: 50)]
    private string $title;

    #[Column(type: "integer")]
    private int $orderBy;

    #[OneToMany(targetEntity: RegistrationStep::class, mappedBy: "registrationStepGroup")]
    #[OrderBy(["orderBy" => "ASC"])]
    private Collection $registrationSteps;

    public function __construct()
    {
        $this->registrationSteps = new ArrayCollection();
    }

    public function getId(): ?int
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

    public function getOrderBy(): ?int
    {
        return $this->orderBy;
    }

    public function setOrderBy(int $orderBy): self
    {
        $this->orderBy = $orderBy;

        return $this;
    }

    /**
     * @return Collection|RegistrationStep[]
     */
    public function getRegistrationSteps(): Collection
    {
        return $this->registrationSteps;
    }

    public function addRegistrationStep(RegistrationStep $registrationStep): self
    {
        if (!$this->registrationSteps->contains($registrationStep)) {
            $this->registrationSteps[] = $registrationStep;
            $registrationStep->setRegistrationStepGroup($this);
        }

        return $this;
    }

    public function removeRegistrationStep(RegistrationStep $registrationStep): self
    {
        if ($this->registrationSteps->removeElement($registrationStep)) {
            // set the owning side to null (unless already changed)
            if ($registrationStep->getRegistrationStepGroup() === $this) {
                $registrationStep->setRegistrationStepGroup(null);
            }
        }

        return $this;
    }
}
