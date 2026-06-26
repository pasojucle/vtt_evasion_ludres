<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SectionRepository::class)]
class Section
{
    #[ORM\Column(type: 'string', length: 100)]
    #[ORM\Id]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $label;

    #[ORM\Column(type: 'string', length: 25)]
    private string $role;

    #[ORM\OneToMany(targetEntity: Parameter::class, mappedBy: 'section')]
    private $parameters;

    public function __construct()
    {
        $this->parameters = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->label;
    }


    public function getId(): ?string
    {
        return $this->id;
    }

    public function setName(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection|Parameter[]
     */
    public function getParameters(): Collection
    {
        return $this->parameters;
    }

    public function addParameter(Parameter $parameter): self
    {
        if (!$this->parameters->contains($parameter)) {
            $this->parameters[] = $parameter;
            $parameter->setSection($this);
        }

        return $this;
    }

    public function removeParameter(Parameter $parameter): self
    {
        if ($this->parameters->removeElement($parameter)) {
            // set the owning side to null (unless already changed)
            if ($parameter->getSection() === $this) {
                $parameter->setSection(null);
            }
        }

        return $this;
    }
}
