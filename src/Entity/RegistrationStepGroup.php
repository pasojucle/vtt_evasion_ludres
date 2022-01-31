<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RegistrationStepGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;

/**
 * @ORM\Entity(repositoryClass=RegistrationStepGroupRepository::class)
 */
class RegistrationStepGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderBy;

    /**
     * @ORM\OneToMany(targetEntity=RegistrationStep::class, mappedBy="registrationStepGroup")
     * @ORM\OrderBy({"orderBy" = "ASC"})
     */
    private $registrationSteps;

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
        if (! $this->registrationSteps->contains($registrationStep)) {
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
