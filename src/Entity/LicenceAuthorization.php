<?php

namespace App\Entity;

use App\Repository\LicenceAuthorizationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LicenceAuthorizationRepository::class)]
class LicenceAuthorization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'licenceAuthorizations')]
    private ?Licence $licence = null;

    #[ORM\ManyToOne(inversedBy: 'licenceAuthorizations')]
    private ?Authorization $authorization = null;

    #[ORM\Column]
    private bool $value = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
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

    public function getAuthorization(): ?Authorization
    {
        return $this->authorization;
    }

    public function setAuthorization(?Authorization $authorization): static
    {
        $this->authorization = $authorization;

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
