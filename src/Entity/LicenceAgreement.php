<?php

namespace App\Entity;

use App\Repository\LicenceAgreementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LicenceAgreementRepository::class)]
class LicenceAgreement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'licenceAgreements')]
    private ?Licence $licence = null;

    #[ORM\ManyToOne(inversedBy: 'licenceAgreements')]
    private ?Agreement $agreement = null;

    #[ORM\Column]
    private bool $agreed = false;

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

    public function getAgreement(): ?Agreement
    {
        return $this->agreement;
    }

    public function setAgreement(?Agreement $agreement): static
    {
        $this->agreement = $agreement;

        return $this;
    }

    public function isAgreed(): bool
    {
        return $this->agreed;
    }

    public function setAgreed(bool $agreed): static
    {
        $this->agreed = $agreed;

        return $this;
    }
}
