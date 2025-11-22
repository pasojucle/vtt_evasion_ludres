<?php

namespace App\Entity;

use App\Repository\ConsentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsentRepository::class)]
class Consent
{
    #[ORM\Id]
    #[ORM\Column(length: 25)]
    private ?string $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?int $registrationForm = null;

    /**
     * @var Collection<int, LicenceConsent>
     */
    #[ORM\OneToMany(targetEntity: LicenceConsent::class, mappedBy: 'consent')]
    private Collection $licenceConsents;

    #[ORM\Column(type: 'LicenceCategory')]
    private ?object $category = null;

    #[ORM\Column(type: 'LicenceMembership')]
    private ?object $membership = null;

    #[ORM\Column(length: 50)]
    private ?string $title = null;

    public function __construct()
    {
        $this->licenceConsents = new ArrayCollection();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getRegistrationForm(): ?int
    {
        return $this->registrationForm;
    }

    public function setRegistrationForm(int $registrationForm): static
    {
        $this->registrationForm = $registrationForm;

        return $this;
    }

    /**
     * @return Collection<int, LicenceConsent>
     */
    public function getLicenceConsents(): Collection
    {
        return $this->licenceConsents;
    }

    public function addLicenceConsent(LicenceConsent $licenceConsent): static
    {
        if (!$this->licenceConsents->contains($licenceConsent)) {
            $this->licenceConsents->add($licenceConsent);
            $licenceConsent->setConsent($this);
        }

        return $this;
    }

    public function removeLicenceConsent(LicenceConsent $licenceConsent): static
    {
        if ($this->licenceConsents->removeElement($licenceConsent)) {
            // set the owning side to null (unless already changed)
            if ($licenceConsent->getConsent() === $this) {
                $licenceConsent->setConsent(null);
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
