<?php

namespace App\Entity;

use App\Entity\Identity;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    public const APPROVAL_RIGHT_TO_THE_IMAGE = 1;
    public const APPROVAL_GOING_HOME_ALONE = 2;

    public const APPROVALS = [
        self::APPROVAL_RIGHT_TO_THE_IMAGE => 'approval.right_to_the_image',
        self::APPROVAL_GOING_HOME_ALONE => 'approval.going_home_alone',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $licenceNumber;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    /**
     * @ORM\OneToMany(targetEntity=Identity::class, mappedBy="user")
     */
    private $identities;

    /**
     * @ORM\OneToOne(targetEntity=Health::class, cascade={"persist", "remove"})
     */
    private $health;

    /**
     * @ORM\OneToMany(targetEntity=Approval::class, mappedBy="user")
     */
    private $approvals;

    /**
     * @ORM\OneToMany(targetEntity=Licence::class, mappedBy="user")
     */
    private $licences;

    /**
     * @ORM\OneToMany(targetEntity=Session::class, mappedBy="user")
     */
    private $sessions;

    /**
     * @ORM\ManyToOne(targetEntity=Level::class, inversedBy="users")
     */
    private $level;

    /**
     * @ORM\Column(type="boolean", options={"default":0})
     */
    private $passwordMustBeChanged = false;

    public function __construct()
    {
        $this->identities = new ArrayCollection();
        $this->approvals = new ArrayCollection();
        $this->licences = new ArrayCollection();
        $this->sessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLicenceNumber(): ?string
    {
        return $this->licenceNumber;
    }

    public function setLicenceNumber(string $licenceNumber): self
    {
        $this->licenceNumber = $licenceNumber;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->licenceNumber;
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
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }
        
        return $this;
    }

    public function removeRole(string $role): self
    {
        $key = array_search($role, $this->roles);

        if (false !== $key) {
            unset($this->roles[$key]);
        }

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

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
            $identity->setUser($this);
        }

        return $this;
    }

    public function removeIdentity(Identity $identity): self
    {
        if ($this->identities->removeElement($identity)) {
            // set the owning side to null (unless already changed)
            if ($identity->getUser() === $this) {
                $identity->setUser(null);
            }
        }

        return $this;
    }

    public function getFirstIdentity(): ?Identity
    {
        if (!$this->identities->isEmpty()) {
            return $this->identities->first();
        }
        return null;
    }

    public function getHealth(): ?Health
    {
        return $this->health;
    }

    public function setHealth(?Health $health): self
    {
        $this->health = $health;

        return $this;
    }

    /**
     * @return Collection|Approval[]
     */
    public function getApprovals(): Collection
    {
        return $this->approvals;
    }

    public function addApproval(Approval $approval): self
    {
        if (!$this->approvals->contains($approval)) {
            $this->approvals[] = $approval;
            $approval->setUser($this);
        }

        return $this;
    }

    public function removeApproval(Approval $approval): self
    {
        if ($this->approvals->removeElement($approval)) {
            // set the owning side to null (unless already changed)
            if ($approval->getUser() === $this) {
                $approval->setUser(null);
            }
        }

        return $this;
    }

    public function getApprovalsGoingHomeAlone(): Collection
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('type', self::APPROVAL_GOING_HOME_ALONE))
        ;
        return $this->approvals->matching($criteria);
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

    public function getSeasonLicence(int $season): ?licence
    {
        if (!$this->licences->isEmpty()) {
            foreach ($this->licences as $licence) {
                if ($season === $licence->getSeason()) {
                    return $licence; 
                }
            }
        }

        return null;
    }

    public function getLastLicence(): ?licence
    {;
        $lastSeason = 1900;
        $lastLicence = null;
        if (!$this->licences->isEmpty()) {
            foreach ($this->licences as $licence) {
                if ($licence->getSeason() > $lastSeason) {
                    $lastSeason = $licence->getSeason(); 
                    $lastLicence = $licence; 
                }
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

    public function isPasswordMustBeChanged(): ?bool
    {
        return $this->passwordMustBeChanged;
    }

    public function setPasswordMustBeChanged(bool $passwordMustBeChanged): self
    {
        $this->passwordMustBeChanged = $passwordMustBeChanged;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->getFirstIdentity()->getName().' '.$this->getFirstIdentity()->getfirstName();
    }
}
