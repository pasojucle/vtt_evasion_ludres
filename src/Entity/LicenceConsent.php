<?php

namespace App\Entity;

use App\Repository\LicenceConsentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LicenceConsentRepository::class)]
class LicenceConsent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'licenceConsents')]
    private ?Licence $licence = null;

    #[ORM\ManyToOne(inversedBy: 'licenceConsents')]
    private ?Consent $consent = null;

    #[ORM\Column]
    private bool $value = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLicence(): ?Licence
    {
        return $this->licence;
    }

    public function setLicence(?Licence $licence): static
    {
        $this->licence = $licence;

        return $this;
    }

    public function getConsent(): ?Consent
    {
        return $this->consent;
    }

    public function setConsent(?Consent $consent): static
    {
        $this->consent = $consent;

        return $this;
    }

    public function getValue(): bool
    {
        return $this->value;
    }

    public function setValue(bool $value): static
    {
        $this->value = $value;

        return $this;
    }

}
