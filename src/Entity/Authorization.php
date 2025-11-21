<?php

namespace App\Entity;

use App\Repository\AuthorizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuthorizationRepository::class)]
class Authorization
{
    #[ORM\Id]
    #[ORM\Column(length: 25)]
    private ?string $id = null;

    #[ORM\Column(length: 25)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 50)]
    private ?string $authorizationMessage = null;

    #[ORM\Column(length: 50)]
    private ?string $rejectionMessage = null;

    #[ORM\Column(length: 50)]
    private ?string $authorizationIcon = null;

    #[ORM\Column(length: 50)]
    private ?string $rejectionIcon = null;

    /**
     * @var Collection<int, LicenceAuthorization>
     */
    #[ORM\OneToMany(targetEntity: LicenceAuthorization::class, mappedBy: 'authorization')]
    private Collection $licenceAuthorizations;

    #[ORM\Column(type: 'LicenceCategory')]
    private ?object $category = null;

    #[ORM\Column(type: 'LicenceMembership')]
    private ?object $membership = null;

    public function __construct()
    {
        $this->licenceAuthorizations = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getAuthorizationMessage(): ?string
    {
        return $this->authorizationMessage;
    }

    public function setAuthorizationMessage(string $authorizationMessage): static
    {
        $this->authorizationMessage = $authorizationMessage;

        return $this;
    }

    public function getRejectionMessage(): ?string
    {
        return $this->rejectionMessage;
    }

    public function setRejectionMessage(string $rejectionMessage): static
    {
        $this->rejectionMessage = $rejectionMessage;

        return $this;
    }

    public function getAuthorizationIcon(): ?string
    {
        return $this->authorizationIcon;
    }

    public function setAuthorizationIcon(string $authorizationIcon): static
    {
        $this->authorizationIcon = $authorizationIcon;

        return $this;
    }

    public function getRejectionIcon(): ?string
    {
        return $this->rejectionIcon;
    }

    public function setRejectionIcon(string $rejectionIcon): static
    {
        $this->rejectionIcon = $rejectionIcon;

        return $this;
    }

    /**
     * @return Collection<int, LicenceAuthorization>
     */
    public function getLicenceAuthorizations(): Collection
    {
        return $this->licenceAuthorizations;
    }

    public function addLicenceAuthorization(LicenceAuthorization $licenceAuthorization): static
    {
        if (!$this->licenceAuthorizations->contains($licenceAuthorization)) {
            $this->licenceAuthorizations->add($licenceAuthorization);
            $licenceAuthorization->setAuthorization($this);
        }

        return $this;
    }

    public function removeLicenceAuthorization(LicenceAuthorization $licenceAuthorization): static
    {
        if ($this->licenceAuthorizations->removeElement($licenceAuthorization)) {
            // set the owning side to null (unless already changed)
            if ($licenceAuthorization->getAuthorization() === $this) {
                $licenceAuthorization->setAuthorization(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?object
    {
        return $this->category;
    }

    public function setCategory(object $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getMembership(): ?object
    {
        return $this->membership;
    }

    public function setMembership(object $membership): static
    {
        $this->membership = $membership;

        return $this;
    }
}
