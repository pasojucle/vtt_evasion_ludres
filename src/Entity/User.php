<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Licence;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'user')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'member' => Member::class,
    'guest' => Guest::class,
])]
abstract class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\Column(type: 'string', length: 25, unique: true, nullable: true)]
    #[Assert\NotBlank(groups: ['registration_user'])]
    protected ?string $licenceNumber = null;

    #[ORM\Column(type: 'json')]
    protected array $roles = [];

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    protected ?Identity $identity = null;

    /**
     * @var ArrayCollection <Licence>
     */
    #[ORM\OneToMany(targetEntity: Licence::class, mappedBy: 'user')]
    protected Collection $licences;

    /**
     * @var ArrayCollection <Session>
     */
    #[ORM\OneToMany(targetEntity: Session::class, mappedBy: 'user')]
    protected Collection $sessions;

    #[ORM\ManyToOne(targetEntity: Level::class, inversedBy: 'users')]
    protected ?Level $level = null;
    
    public function __construct()
    {
        $this->licences = new ArrayCollection();
        $this->sessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
      * @see UserInterface
      */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        $key = array_search($role, $this->roles, true);

        if (false !== $key) {
            unset($this->roles[$key]);
        }

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function isGuest(): bool
    {
        return $this instanceof Guest;
    }

    abstract public function getMainIdentity(): ?Identity;

    abstract public function getContactEmail(): ?string;

    public function getLicenceNumber(): ?string
    {
        return $this->licenceNumber;
    }

    public function setLicenceNumber(?string $licenceNumber): self
    {
        $this->licenceNumber = $licenceNumber;

        return $this;
    }

    public function getIdentity(): ?Identity
    {
        return $this->identity;
    }

    public function setIdentity(?Identity $identity): static
    {
        // unset the owning side of the relation if necessary
        if ($identity === null && $this->identity !== null) {
            $this->identity->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($identity !== null && $identity->getUser() !== $this) {
            $identity->setUser($this);
        }

        $this->identity = $identity;

        return $this;
    }

    /**
     * @return Collection|Licence[]
     */
    public function getLicences(): Collection
    {
        return $this->licences;
    }

    public function addLicence(Licence $licence): self
    {
        if (!$this->licences->contains($licence)) {
            $this->licences[] = $licence;
            $licence->setUser($this);
        }

        return $this;
    }

    public function removeLicence(Licence $licence): self
    {
        if ($this->licences->removeElement($licence)) {
            // set the owning side to null (unless already changed)
            if ($licence->getUser() === $this) {
                $licence->setUser(null);
            }
        }

        return $this;
    }

    public function getSeasonLicence(int $season): ?Licence
    {
        foreach ($this->licences as $licence) {
            if ($season === $licence->getSeason()) {
                return $licence;
            }
        }

        return null;
    }

    public function getLastLicence(): ?Licence
    {
        $lastSeason = 1900;
        $lastLicence = null;
        foreach ($this->licences as $licence) {
            if ($licence->getSeason() > $lastSeason) {
                $lastSeason = $licence->getSeason();
                $lastLicence = $licence;
            }
        }

        return $lastLicence;
    }
    
    /**
     * @return Collection|Session[]
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): self
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions[] = $session;
            $session->setUser($this);
        }

        return $this;
    }

    public function removeSession(Session $session): self
    {
        if ($this->sessions->removeElement($session)) {
            // set the owning side to null (unless already changed)
            if ($session->getUser() === $this) {
                $session->setUser(null);
            }
        }

        return $this;
    }

    public function getDoneSessions(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('isPresent', true))
        ;

        return $this->sessions->matching($criteria);
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(?Level $level): self
    {
        $this->level = $level;

        return $this;
    }
}
