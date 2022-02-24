<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ParameterGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\GeneratedValue;

#[Entity(repositoryClass: ParameterGroupRepository::class)]
class ParameterGroup
{
    #[Column(type: "integer")]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: "string", length: 50)]
    private string $name;

    #[Column(type: "string", length: 255)]
    private string $label;

    #[Column(type: "string", length: 25)]
    private string $role;

    #[OneToMany(targetEntity: Parameter::class, mappedBy: "parameterGroup")]
    private $parameters;

    public function __construct()
    {
        $this->parameters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
            $parameter->setParameterGroup($this);
        }

        return $this;
    }

    public function removeParameter(Parameter $parameter): self
    {
        if ($this->parameters->removeElement($parameter)) {
            // set the owning side to null (unless already changed)
            if ($parameter->getParameterGroup() === $this) {
                $parameter->setParameterGroup(null);
            }
        }

        return $this;
    }
}
