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
    private int $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isCompensable = false;

    #[ORM\OneToMany(mappedBy: 'bikeRideType', targetEntity: BikeRide::class)]
    private Collection $bikeRides;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isRegistrable = true;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isSchool = false;

    #[ORM\OneToMany(mappedBy: 'bikeRideType', targetEntity: Indemnity::class)]
    private $indemnities;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $useLevels = true;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $showMemberList = false;

    public function __construct()
    {
        $this->bikeRides = new ArrayCollection();
        $this->indemnities = new ArrayCollection();
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

    public function isSchool(): ?bool
    {
        return $this->isSchool;
    }

    public function setIsSchool(bool $isSchool): self
    {
        $this->isSchool = $isSchool;

        return $this;
    }

    /**
     * @return Collection|Indemnity[]
     */
    public function getIndemnities(): Collection
    {
        return $this->indemnities;
    }

    public function addIndemnity(Indemnity $indemnity): self
    {
        if (!$this->indemnities->contains($indemnity)) {
            $this->indemnities[] = $indemnity;
            $indemnity->setBikeRideType($this);
        }

        return $this;
    }

    public function removeIndemnity(Indemnity $indemnity): self
    {
        if ($this->indemnities->removeElement($indemnity)) {
            // set the owning side to null (unless already changed)
            if ($indemnity->getBikeRideType() === $this) {
                $indemnity->setBikeRideType(null);
            }
        }

        return $this;
    }

    public function isUseLevels(): bool
    {
        return $this->useLevels;
    }

    public function setuseLevel(bool $useLevels): self
    {
        $this->useLevels = $useLevels;

        return $this;
    }

    public function isShowMemberList(): bool
    {
        return $this->showMemberList;
    }

    public function setShowMemberList(bool $showMemberList): self
    {
        $this->showMemberList = $showMemberList;

        return $this;
    }
}
