<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
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
    private $active;

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
     * @ORM\OneToOne(targetEntity=Licence::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $licence;



    public function __construct()
    {
        $this->identities = new ArrayCollection();
        $this->approvals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getlicenceNumber(): ?string
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

    public function getLicence(): ?Licence
    {
        return $this->licence;
    }

    public function setLicence(?Licence $licence): self
    {
        // unset the owning side of the relation if necessary
        if ($licence === null && $this->licence !== null) {
            $this->licence->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($licence !== null && $licence->getUser() !== $this) {
            $licence->setUser($this);
        }

        $this->licence = $licence;

        return $this;
    }
}
