<?php

namespace App\Entity;

use App\Repository\BikeRideTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BikeRideTypeRepository::class)]
class BikeRideType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $isCompensable;

    #[ORM\OneToMany(mappedBy: 'bikeRideType', targetEntity: BikeRide::class)]
    private $bikeRides;

    #[ORM\Column(type: 'boolean')]
    private $isRegistrable;

    public function __construct()
    {
        $this->bikeRides = new ArrayCollection();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function isCompensable(): ?bool
    {
        return $this->isCompensable;
    }

    public function setIsCompensable(bool $isCompensable): self
    {
        $this->isCompensable = $isCompensable;

        return $this;
    }

    /**
     * @return Collection|BikeRide[]
     */
    public function getBikeRides(): Collection
    {
        return $this->bikeRides;
    }

    public function addBikeRide(BikeRide $bikeRide): self
    {
        if (!$this->bikeRides->contains($bikeRide)) {
            $this->bikeRides[] = $bikeRide;
            $bikeRide->setBikeRideType($this);
        }

        return $this;
    }

    public function removeBikeRide(BikeRide $bikeRide): self
    {
        if ($this->bikeRides->removeElement($bikeRide)) {
            // set the owning side to null (unless already changed)
            if ($bikeRide->getBikeRideType() === $this) {
                $bikeRide->setBikeRideType(null);
            }
        }

        return $this;
    }

    public function isRegistrable(): ?bool
    {
        return $this->isRegistrable;
    }

    public function setIsRegistrable(bool $isRegistrable): self
    {
        $this->isRegistrable = $isRegistrable;

        return $this;
    }
}
