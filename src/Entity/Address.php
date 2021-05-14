<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AddressRepository::class)
 */
class Address
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $town;

    /**
     * @ORM\OneToMany(targetEntity=Identity::class, mappedBy="address")
     */
    private $identities;

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

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function setTown(string $town): self
    {
        $this->town = $town;

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
}
