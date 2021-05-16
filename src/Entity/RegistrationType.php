<?php

namespace App\Entity;

use App\Repository\RegistrationTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RegistrationTypeRepository::class)
 */
class RegistrationType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $title;

    /**
     * @ORM\ManyToMany(targetEntity=RegistrationStep::class, mappedBy="types")
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
            $registrationStep->addType($this);
        }

        return $this;
    }

    public function removeRegistrationStep(RegistrationStep $registrationStep): self
    {
        if ($this->registrationSteps->removeElement($registrationStep)) {
            $registrationStep->removeType($this);
        }

        return $this;
    }
}
