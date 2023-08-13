<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[Column(type: 'integer')]
    #[Id, GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[Column(type: 'string', length: 255)]
    private string $street = '';

    #[Column(type: 'string', length: 5)]
    private string $postalCode = '';

    #[Column(type: 'string', length: 100)]
    private string $town = '';

    #[OneToMany(targetEntity: Identity::class, mappedBy: 'address')]
    private Collection $identities;

    #[ManyToOne(targetEntity: Commune::class, inversedBy: 'addresses')]
    private ?Commune $commune = null;

    public function __construct()
    {
        $this->identities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = ($street) ? $street : '';

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = ($postalCode) ? $postalCode : '';

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(?string $town): self
    {
        $this->town = ($town) ? $town : '';

        return $this;
    }

    /**
     * @return Collection|Identity[]
     */
    public function getIdentities(): Collection
    {
        return $this->identities;
    }

    public function addIdentity(Identity $identity): self
    {
        if (!$this->identities->contains($identity)) {
            $this->identities[] = $identity;
            $identity->setAddress($this);
        }

        return $this;
    }

    public function removeIdentity(Identity $identity): self
    {
        if ($this->identities->removeElement($identity)) {
            // set the owning side to null (unless already changed)
            if ($identity->getAddress() === $this) {
                $identity->setAddress(null);
            }
        }

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->getStreet()) && empty($this->getPostalCode()) && empty($this->getTown());
    }


    public function getCommune(): ?Commune
    {
        return $this->commune;
    }

    public function setCommune(?Commune $commune): self
    {
        $this->commune = $commune;

        return $this;
    }
}
