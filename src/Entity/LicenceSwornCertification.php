<?php

namespace App\Entity;

use App\Repository\LicenceSwornCertificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LicenceSwornCertificationRepository::class)]
class LicenceSwornCertification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'licenceSwornCertifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Licence $licence = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?SwornCertification $swornCertification = null;

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

    public function getSwornCertification(): ?SwornCertification
    {
        return $this->swornCertification;
    }

    public function setSwornCertification(?SwornCertification $swornCertification): static
    {
        $this->swornCertification = $swornCertification;

        return $this;
    }

    public function isValue(): bool
    {
        return $this->value;
    }

    public function setValue(bool $value): static
    {
        $this->value = $value;

        return $this;
    }
}
