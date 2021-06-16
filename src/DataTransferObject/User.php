<?php

namespace App\DataTransferObject;

use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\User as UserEntity;
use Symfony\Component\Form\DataTransformerInterface;

class User {

    public function __construct(UserEntity $user)
    {
        $this->user = $user;
        $this->memberIdentity = null;
        $this->kinshipIdentity = null;
        $this->setIdentities();
    }

    public function setIdentities(): self
    {
        $identities = $this->user->getIdentities();
        if (1 < $identities->count()) {
            foreach ($identities as $identity) {
                if (null === $identity->getKinship()) {
                    $this->memberIdentity = $identity;

                } else {
                    $this->kinshipIdentity = $identity;
                }
            }
        } else {
            $this->memberIdentity = $identities->first();
            $this->kinshipIdentity = null;
        }

        return $this;
    }

    public function getMemberIdentity(): ?Identity
    {
        return $this->memberIdentity;
    }

    public function getKinshipIdentity(): ?Identity
    {
        return $this->kinshipIdentity;
    }

    public function getFullName(): string
    {
        if ($this->memberIdentity) {
            return ($this->kinshipIdentity)
                ? $this->kinshipIdentity->getName().' '.$this->kinshipIdentity->getFirstName()
                : $this->memberIdentity->getName().' '.$this->memberIdentity->getFirstName();
        }
        return '';
    }

    public function getFullNameChildren()
    {
        if ($this->kinshipIdentity && $this->memberIdentity) {
            return $this->memberIdentity->getName().' '.$this->memberIdentity->getFirstName();
        }
        return '';
    }

    public function getBirthDate(): ?string
    {
        if ($this->memberIdentity) {
            $bithDate = ($this->kinshipIdentity)
                ? $this->kinshipIdentity->getBirthDate()
                : $this->memberIdentity->getBirthDate();
            return ($bithDate) ? $bithDate->format('d/m/Y') : null;
        }
        return '';
    }

    public function getBirthDateChildren(): ?string
    {
        if ($this->kinshipIdentity && $this->memberIdentity) {
            $bithDate = $this->memberIdentity->getBirthDate();
            return ($bithDate) ? $bithDate->format('d/m/Y') : null;
        }
        return '';
    }

    public function getCoverage(string $season): ?int
    {
        $seasonLicence = $this->user->getSeasonLicence($season);
        return (null !== $seasonLicence) ? $seasonLicence->getCoverage() : null;
    }

    public function getSeasonLicence(string $season): array
    {
        $seasonLicence = [];
        $licence = $this->user->getSeasonLicence($season);
        if (null !== $licence) {
            $seasonLicence = [
                'isTesting' => $licence->isTesting(),
                'coverage' => $licence->getCoverage(),
                'hasFamilyMember' => $licence->getAdditionalFamilyMember(),
                'category' => $licence->getCategory(),
            ];  
        } 
        return $seasonLicence;
    }

    public function getKinShip(): array
    {
        $kinShip = [];
        if ($this->kinshipIdentity) {
            $kinShip = [
                'fullName' => $this->kinshipIdentity->getName().' '.$this->kinshipIdentity->getFirstName(),
                'type' => Identity::KINSHIPS[$this->kinshipIdentity->getKinShip()] ,
                'address' => $this->kinshipIdentity->getAddress(),
                'email' => $this->kinshipIdentity->getEmail(),
                'phone' => implode(' - ', array_filter([$this->kinshipIdentity->getMobile(), $this->kinshipIdentity->getPhone()])),
            ];
            
        }
        return $kinShip;
    }

    public function getMember(): array
    {
        $member = [];
        if ($this->memberIdentity) {
            $bithDate = $this->memberIdentity->getBirthDate();
            $member = [
                'fullName' => $this->memberIdentity->getName().' '.$this->memberIdentity->getFirstName(),
                'birthDateAndPlace' => ($bithDate) ? $bithDate->format('d/m/Y').' à '.$this->memberIdentity->getBirthPlace() : null,
                'address' => $this->memberIdentity->getAddress(),
                'email' => $this->memberIdentity->getEmail(),
                'phone' => implode(' - ', array_filter([$this->memberIdentity->getMobile(), $this->memberIdentity->getPhone()])),
                'picture' => $this->memberIdentity->getPicture(),
            ];
            
        }
        return $member;
    }

    public function isNewMember(): bool
    {
        return 1 < $this->user->getLicences()->count();
    }

    public function getHealth(): array
    {
        /** var Healt $health */
        $health = $this->user->getHealth();
        if (null !== $health) {
            $tetanusBoosterDate = $health->getTetanusBooster();
            $allDiseases = $health->getDiseases();
            $diseases = [];
            foreach ($allDiseases as $disease) {
                if (null !== $disease->getTitle() || null !== $disease->getCurentTreatment() || null !== $disease->getEmergencyTreatment()) {
                    $diseases[$disease->getType()][] = [
                        'label' => $disease->getLabel(),
                        'title' => $disease->getTitle(),
                        'curentTreatment' => $disease->getCurentTreatment(),
                        'emergencyTreatment' => $disease->getEmergencyTreatment(),
                    ];
                }
            }
            $health = [
                'socialSecurityNumber' => $health->getSocialSecurityNumber(),
                'mutualCompany' => $health->getMutualCompany(),
                'mutualNumber' => $health->getMutualNumber(),
                'bloodGroup' => $health->getBloodGroup(),
                'tetanusBooster' => ($tetanusBoosterDate) ? $tetanusBoosterDate->format('d/m/Y') : null,
                'doctorName' => $health->getDoctorName(),
                'doctorAddress' => $health->getDoctorAddress(),
                'doctorTown' => $health->getDoctorTown(),
                'doctorPhone' => $health->getDoctorPhone(),
                'phonePicto' => 'iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAC3HpUWHRSYXcgcHJvZmlsZSB0eXBlIGV4aWYAAHja7ZdNktwgDIX3nCJHQBJC4jjYQFVukOPnYdPu6Z7OVOVnkUWbMmBZPECfTM+E/uP7CN9wUZEYkprnknPElUoqXNHxeF5nSzEd9fmQ1jt6tIfrBcMkaOV8zH35V9j1PsCWP22P9mD70vEltF7cBGXOzOgsP19Cwqf9tpBQ1riaPmxn3eO2RTub5+dkCEZT6AkH7kISUfucReZNUtFm1CSJbxaWhFpEX8cuXN2n4F29p9jFuuzyGIoQ83LITzFadtLXsTsi9EDtPvPDC5Nris+xG83H6OfuasqIVA5rU7etHD04bgilHMMyiuFW9O0oBcWxxR3EGmhuKHugQoxoD0rUqNKgfrQ77Vhi4s6GlnlHxKfNxbjwDhgAMQsNNinSgjh47KAmMPO1FjrmLcd8OzlmbgRPJogRRnwq4ZXxT8olNMZMXaLoV6ywLp45jWVMcrOGF4DQWDHVI75HCR/yJn4AKyCoR5gdG6xxOyU2pXtuycFZ4KcxhXh+GmRtCSBEmFuxGBIQiJlEKVM0ZiNCHB18KlaOVOcNBEiVG4UBNiIZcJzn3BhjdPiy8mnG0QIQio/GgKZIBayUFPljyZFDVUVTUNWspq5Fa5acsuacLc8zqppYMrVsZm7FqosnV89u7l68Fi6CI0xLLhaKl1JqxaQV0hWjKzxq3XiTLW265c0238pWd6TPnnbd826772WvjZs0fP4tNwvNW2m1U0cq9dS1527de+l1INeGjDR05GHDRxn1oraoPlKjJ3JfU6NFbRJLh5/dqcFsdpOgeZzoZAZinAjEbRJAQvNkFp1S4kluMouF5yHFoEY64TSaxEAwdWIddLG7k/uSW9D0W9z4V+TCRPcvyIWJbpH7zO0FtVaPXxQ5AM2vcMY0ysDBBofulb3O36Q/bsPfCryF3kJvobfQW+gt9Bb6f4QG/njAv5rhJ8n4kT5PdD3IAAAABmJLR0QAEwAgAD4nLw1mAAAACXBIWXMAAA3XAAAN1wFCKJt4AAAAB3RJTUUH5QYOExgj295WAAAAAURJREFUOMut1TlKBGEQBeCv3XBBBBHELRYz1wuIqSCI4h0MFAwMPYBgJngHM8HQWBDRxFxEEVyDQRCXmTawRpphGKbHKSjo/qFfvVdV/+vEb7SiC4nGIsU7ignmsI7hfwLeYx8uUIzD/2QRFwlKwSzFR+QDXjGJzpxM/yrcYTNA+jGC4yiYh+nfwxnGKiouo5AHsCXzcQ+6KwBPcJ5nOlnAIYxm3ltDdlcjI0+jVztoj/NpXOK7UckJ1oKVAGqrUJGLYYpP7IbMFqzgpgqTUj1TLucjVoNdG5Zwha9Y3lNs4QjPQaImYIprLKIjhjOBg9jLySjUgxnsxSWoCVjCLTbQF/3txECVno6HgpqA5XzDIRYCuFrM46VewHI+hNwNTMXV7I0i29khZc2hnm34DPN4CrsqYBaDWXNoun011WCTZv8CfgAlecavwAWn+wAAAABJRU5ErkJggg==',
            'diseases' => $diseases,
            ];
            
        }
        return $health;
    }
}