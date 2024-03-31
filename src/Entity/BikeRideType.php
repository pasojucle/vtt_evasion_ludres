<?php

namespace App\Entity;

use App\Repository\BikeRideTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BikeRideTypeRepository::class)]
class BikeRideType
{
    public const REGISTRATION_NONE = 0;
    public const REGISTRATION_SCHOOL = 1;
    public const REGISTRATION_CLUSTERS = 2;

    public const REGISTRATIONS = [
        self::REGISTRATION_NONE => 'bike_ride_type.registration.none',
        self::REGISTRATION_SCHOOL => 'bike_ride_type.registration.school',
        self::REGISTRATION_CLUSTERS => 'bike_ride_type.registration.clusters',
    ];


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

    #[ORM\OneToMany(mappedBy: 'bikeRideType', targetEntity: Indemnity::class)]
    private $indemnities;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $useLevels = true;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $showMemberList = false;

    #[ORM\Column(type: 'json')]
    private array $clusters = [];

    #[ORM\Column(type: 'integer', options: ['default' => self::REGISTRATION_NONE])]
    private int $registration = self::REGISTRATION_NONE;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $needFramers = false;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $closingDuration = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $displayBikeKind = false;

    #[ORM\ManyToMany(targetEntity: Message::class)]
    private Collection $messages;

    public function __construct()
    {
        $this->bikeRides = new ArrayCollection();
        $this->indemnities = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
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

    public function setuseLevels(bool $useLevels): self
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

    public function getClusters(): array
    {
        return $this->clusters;
    }

    public function setClusters(?array $clusters): self
    {
        $this->clusters = $clusters;

        return $this;
    }

    public function getRegistration(): ?int
    {
        return $this->registration;
    }

    public function setRegistration(int $registration): self
    {
        $this->registration = $registration;

        return $this;
    }

    public function isNeedFramers(): bool
    {
        return $this->needFramers;
    }

    public function setNeedFramers(bool $needFramers): self
    {
        $this->needFramers = $needFramers;

        return $this;
    }

    public function getClosingDuration(): ?int
    {
        return $this->closingDuration;
    }

    public function setClosingDuration(int $closingDuration): self
    {
        $this->closingDuration = $closingDuration;

        return $this;
    }

    public function isDisplayBikeKind(): bool
    {
        return $this->displayBikeKind;
    }

    public function setDisplayBikeKind(bool $displayBikeKind): static
    {
        $this->displayBikeKind = $displayBikeKind;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        $this->messages->removeElement($message);

        return $this;
    }
}
