<?php

namespace App\Entity;

use App\Repository\CommuneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ReflectionProperty;

#[ORM\Entity(repositoryClass: CommuneRepository::class)]
class Commune
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 10)]
    private string $id;

    #[ORM\Column(type: 'string', length: 75)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: Department::class, inversedBy: 'communes')]
    private ?Department $department = null;

    #[ORM\OneToMany(mappedBy: 'commune', targetEntity: Address::class)]
    private Collection $addresses;

    #[ORM\OneToMany(mappedBy: 'birthCommune', targetEntity: Identity::class)]
    private Collection $identities;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->identities = new ArrayCollection();
    }

    public function __toString(): string
    {
        if ($this->exists()) {
            return $this->name . ' - ' . $this->department->getId() . ' ' . $this->department->getName();
        }

        return $this->name;
    }


    
    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @return Collection<int, Address>
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses[] = $address;
            $address->setCommune($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->removeElement($address)) {
            // set the owning side to null (unless already changed)
            if ($address->getCommune() === $this) {
                $address->setCommune(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Identity>
     */
    public function getIdentities(): Collection
    {
        return $this->identities;
    }

    public function addIdentity(Identity $identity): self
    {
        if (!$this->identities->contains($identity)) {
            $this->identities[] = $identity;
            $identity->setBirthCommune($this);
        }

        return $this;
    }

    public function removeIdentity(Identity $identity): self
    {
        if ($this->identities->removeElement($identity)) {
            // set the owning side to null (unless already changed)
            if ($identity->getBirthCommune() === $this) {
                $identity->setBirthCommune(null);
            }
        }

        return $this;
    }
    
    public function exists(): bool
    {
        $property = new ReflectionProperty(self::class, 'id');
        $property->setAccessible(true);
        return $property->isInitialized($this);
    }
}
