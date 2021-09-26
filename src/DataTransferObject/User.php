<?php

namespace App\DataTransferObject;

use DateTime;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\Session;
use App\Entity\Identity;
use App\Entity\User as UserEntity;

class User {

    public function __construct(UserEntity $user, int $currentSeason, array $seasonsStatus)
    {
        $this->user = $user;
        $this->currentSeason = $currentSeason;
        $this->seasonsStatus = $seasonsStatus;
        $this->memberIdentity = null;
        $this->kinshipIdentity = null;
        $this->secondKinshipIdentity = null;
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
                    if (null !== $identity->getBirthDate() && null !== $identity->getEmail()) {
                        $this->kinshipIdentity = $identity;
                    } else {
                        $this->secondKinshipIdentity = $identity;
                    }
                }
            }
        } else {
            $this->memberIdentity = $identities->first();
            $this->kinshipIdentity = null;
        }

        return $this;
    }

    public function getId(): int
    {
        return $this->user->getId();
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

    public function getCoverage(): ?int
    {
        $seasonLicence = $this->user->getSeasonLicence($this->currentSeason);
        return (null !== $seasonLicence) ? $seasonLicence->getCoverage() : null;
    }

    public function getSeasonLicence(): array
    {
        $licence = $this->user->getSeasonLicence($this->currentSeason);
        
        return $this->getLicenceArray($licence);
    }

    public function getLastLicence(): array
    {
        $licence = $this->user->getLastLicence();

        return $this->getLicenceArray($licence);
    }

    public function getKinShip(): array
    {
        $kinShip = [];
        if ($this->kinshipIdentity) {
            $address = (null !== $this->kinshipIdentity->getAddress() && !$this->kinshipIdentity->getAddress()->isEmpty()) ? $this->kinshipIdentity->getAddress() : $this->getMemberIdentity()->getAddress();
            $kinShip = [
                'fullName' => $this->kinshipIdentity->getName().' '.$this->kinshipIdentity->getFirstName(),
                'type' => Identity::KINSHIPS[$this->kinshipIdentity->getKinShip()] ,
                'address' => $address,
                'email' => $this->kinshipIdentity->getEmail(),
                'phone' => implode(' - ', array_filter([$this->kinshipIdentity->getMobile(), $this->kinshipIdentity->getPhone()])),
            ];
            
        }
        return $kinShip;
    }

    public function getSecondKinShip(): array
    {
        $kinShip = [];
        if ($this->secondKinshipIdentity) {
            $kinShip = [
                'fullName' => $this->secondKinshipIdentity->getName().' '.$this->secondKinshipIdentity->getFirstName(),
                'type' => Identity::KINSHIPS[$this->secondKinshipIdentity->getKinShip()] ,
                'phone' => $this->secondKinshipIdentity->getMobile(),
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
                'name' => $this->memberIdentity->getName(),
                'firstName' => $this->memberIdentity->getFirstName(),
                'fullName' => $this->memberIdentity->getName().' '.$this->memberIdentity->getFirstName(),
                'birthDate' => ($bithDate) ? $bithDate->format('d/m/Y'): null,
                'birthPlace' => $this->memberIdentity->getBirthPlace().' ('.$this->memberIdentity->getBirthDepartment().')',
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
        return 2 > $this->user->getLicences()->count();
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

    public function isMedicalCertificateRequired(): string
    {
        $message = '';
        $licence = $this->user->getSeasonLicence($this->currentSeason);

        if (null !== $licence && $licence->isMedicalCertificateRequired()) {
            $message = 'Vous devez joindre un certificat médical daté DE MOINS DE 12 MOIS de non contre-indication à la pratique du VTT';
        }
        return $message;
    }

    public function getApprovals()
    {
        $approvals = [];
        if (!$this->user->getApprovals()->isEmpty()) {
            foreach ($this->user->getApprovals() as $approval) {
                $string = ($approval->getValue()) ? 'autorise' : 'n\'autorise pas';
                if (UserEntity::APPROVAL_GOING_HOME_ALONE == $approval->getType()) {
                    $approvals['goingHomeAlone'] = ['value' => $approval->getValue(), 'string' => $string];
                }
                if (UserEntity::APPROVAL_RIGHT_TO_THE_IMAGE == $approval->getType()) {
                    $approvals['rightToImage'] =  ['value' => $approval->getValue(), 'string' => $string];
                }
            }
        }

        return $approvals;
    }

    public function getApprovalGoingHome(): ?array
    {
        $approvalGoingHome = null;
        if (!$this->user->getApprovals()->isEmpty()) {
            foreach ($this->user->getApprovals() as $approval) {
                if (UserEntity::APPROVAL_GOING_HOME_ALONE == $approval->getType()) {
                    $approvalGoingHome = ($approval->getValue()) 
                        ? ['class' => 'success', 'message' => 'Autorisé à rentrer seul']
                        : ['class' => 'alert-danger', 'message' => 'Pas autorisé à rentrer seul'];
                }
            }
        }
        return $approvalGoingHome;
    }

    public function getLevel(): ?Level
    {
        return $this->user->getLevel();
    }

    public function getLicenceNumber(): string
    {
        return $this->user->getLicenceNumber();
    }

    public function getBikeRides(): array
    {
        $bikeRides = [];
        $today = new DateTime();

        $sessions = $this->user->getSessions();
        if (!$sessions->isEmpty()) {
            foreach ($sessions as $session) {
                $event = $session->getCluster()->getEvent();
                $startAt = DateTime::createFromFormat('Y-m-d H:i:s', $event->getStartAt()->format('Y-m-d').' 14:00:00');
                if ($today <= $startAt) {
                    $bikeRides[] = [
                        'event' => $session->getCluster()->getEvent(),
                        'availability' => $session->getAvailabilityToView(),
                        'sessionId' => $session->getId(),
                    ];
                }
            }
        }

        return $bikeRides;
    }

    public function isMember(): bool
    {
        $type = (null !== $this->user->getLevel()) ? $this->user->getLevel()->getType() : null;
        return $type === Level::TYPE_MEMBER;
    }

    public function isFramer(): bool
    {
        $type = (null !== $this->user->getLevel()) ? $this->user->getLevel()->getType() : null;
        return $type === Level::TYPE_FRAME;
    }

    Private function getLicenceArray(?Licence $licence): array
    {
        $licenceArray = [];
        $statusClassArray = [
            Licence::STATUS_NONE => 'alert-black',
            Licence::STATUS_WAITING_RENEW => 'alert-danger',
            Licence::STATUS_IN_PROCESSING=> 'alert-warning',
            Licence::STATUS_WAITING_VALIDATE => 'alert-warning',
            Licence::STATUS_TESTING => 'success-test',
            Licence::STATUS_VALID => 'success',
        ];

        if (null !== $licence) {
            $status = $licence->getStatus();
            if ($licence->getSeason() !== $this->currentSeason) {
                if ($this->seasonsStatus[Licence::STATUS_NONE] >= $licence->getSeason()) {
                    $status = Licence::STATUS_NONE;
                }
                if ($this->seasonsStatus[Licence::STATUS_WAITING_RENEW] === $licence->getSeason()) {
                    $status = Licence::STATUS_WAITING_RENEW;
                }
            }

            $licenceArray = [
                'id' => $licence->getId(),
                'createdAt' => ($licence->getCreatedAt()) ? $licence->getCreatedAt()->format('d/m/Y') : null,
                'season' => $licence->getSeason(),
                'isFinal' => $licence->isFinal(),
                'coverage' => (null !== $licence->getCoverage()) ? $licence->getCoverage() : null,
                'coverageStr' => (!empty($licence->getCoverage())) ? Licence::COVERAGES[$licence->getCoverage()] : null,
                'hasFamilyMember' => $licence->getAdditionalFamilyMember(),
                'category' => $licence->getCategory(),
                'statusClass' => $statusClassArray[$status],
                'status' => $status,
                'statusStr' => Licence::STATUS[$status],
                'type' => (!empty($licence->getType())) ? Licence::TYPES[$licence->getType()] : null,
                'lock' => $licence->getSeason() !== $this->currentSeason,
            ];  
        }

        return $licenceArray;
    }

    public function isEndTesting(): bool
    {
        if (!empty($this->getSeasonLicence()) && !$this->getSeasonLicence()['isFinal']) {
            $count = (null !== $this->user->getSessions()) ? $this->user->getSessions()->count() : 0;
            
            return 2 < $count;
        }
        return false;
    }

    public function testingBikeRides(): ?int
    {
        if (!empty($this->getSeasonLicence()) && !$this->getSeasonLicence()['isFinal']) {
            $count = (null !== $this->user->getSessions()) ? $this->user->getSessions()->count() : 0;

            return $count;
        }
        return null;
    }

    public function getContactEmail(): string
    {
        $member = $this->getMember();
        $licence = $this->getLastLicence();
        if (!empty($licence) && $licence['category'] === Licence::CATEGORY_MINOR) {
            $member = $this->getKinShip();
        }

        return $member['email'];
    }
}