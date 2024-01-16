<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\IdentityDto;
use App\Entity\Identity;
use App\Repository\RegistrationChangeRepository;
use App\Service\ProjectDirService;
use App\Service\SeasonService;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;

class IdentityDtoTransformer
{
    public function __construct(
        private AddressDtoTransformer $addressDtoTransformer,
        private ProjectDirService $projectDirService,
        private RegistrationChangeRepository $registrationChangeRepository,
        private SeasonService $seasonService,
    ) {
    }

    public function fromEntity(Identity $identity, ?array $changes = null): IdentityDto
    {
        $identityDto = new IdentityDto();

        $bithDate = $identity->getBirthDate();
        $identityDto->id = $identity->getId();
        $identityDto->name = $identity->getName();
        $identityDto->firstName = $identity->getFirstName();
        $identityDto->fullName = $identity->getName() . ' ' . $identity->getFirstName();
        $identityDto->birthDate = ($bithDate) ? $bithDate->format('d/m/Y') : null;
        $identityDto->birthPlace = $this->getBirthplace($identity);
        $identityDto->address = ($identity->getAddress()) ? $this->addressDtoTransformer->fromEntity($identity->getAddress(), $changes) : null;
        $identityDto->email = $identity->getEmail();
        $identityDto->phone = implode(' - ', array_filter([$identity->getMobile(), $identity->getPhone()]));
        $identityDto->emergencyPhone = $identity->getEmergencyPhone();
        $identityDto->phonesAnchor = $this->getPhonesAnchor($identity);
        $identityDto->picture = $this->getPicture($identity->getPicture());
        $identityDto->type = (null !== $identity->getKinShip()) ? Identity::KINSHIPS[$identity->getKinShip()] : null;

        $identityDto->age = $this->getAge($bithDate);

        if ($changes) {
            $this->formatChanges($changes, $identityDto);
        }

        return $identityDto;
    }


    public function headerFromEntity(Identity $identity, ?array $changes = null): IdentityDto
    {
        $identityDto = new IdentityDto();

        $identityDto->id = $identity->getId();
        $identityDto->fullName = $identity->getName() . ' ' . $identity->getFirstName();

        return $identityDto;
    }


    public function fromEntities(Collection $identityEntities, ?array $changes = null): array
    {
        $identities = [];
        /** @var Identity $identity */
        foreach ($identityEntities as $identity) {
            $identities[$identity->getType()] = $this->fromEntity($identity, $changes);
        }
        $this->setKinshipAddress($identities);


        return $identities;
    }

    private function getAge(?DateTimeInterface $birthDate): ? int
    {
        if (null !== $birthDate) {
            $today = new DateTime();
            $age = $today->diff($birthDate);

            return (int) $age->format('%y');
        }

        return null;
    }

    private function setKinshipAddress(array &$identities): void
    {
        $kinships = [Identity::TYPE_KINSHIP, Identity::TYPE_SECOND_CONTACT];
        $memberAddress = $identities[Identity::TYPE_MEMBER]->address;
        foreach ($kinships as $kinship) {
            if (array_key_exists($kinship, $identities) && null !== $identities[$kinship]) {
                $identities[$kinship]->address = $memberAddress;
            }
        }
    }

    private function getPicture(?string $picture): string
    {
        return (null !== $picture) ? $this->projectDirService->dir('', 'upload', $picture) : '/images/default-user-picture.jpg';
    }

    private function getPhoneAnchor(?string $phone): string
    {
        return ($phone) ? '<a class="phone" href="tel:' . $phone . '">' . $phone . '</a>' : '';
    }

    private function getPhonesAnchor(Identity $identity): string
    {
        return implode(' - ', array_filter([$this->getPhoneAnchor($identity->getMobile()), $this->getPhoneAnchor($identity->getPhone())]));
    }

    private function getBirthplace(Identity $identity): string
    {
        $birthCommune = $identity->getBirthCommune();
        
        if ($birthCommune) {
            return ($birthCommune->getDepartment())
                ? $birthCommune->getName() . ' (' . $birthCommune->getDepartment()->getName() . ')'
                : $birthCommune->getName();
        }

        return $identity->getBirthPlace() . ' (' . $identity->getBirthDepartment() . ')';
    }

    private function formatChanges(array $changes, IdentityDto &$identityDto): void
    {
        if (array_key_exists('Identity', $changes) && array_key_exists($identityDto->id, $changes['Identity'])) {
            $properties = array_keys($changes['Identity'][$identityDto->id]->getValue());
            
            foreach ($properties as $property) {
                if ('mobile' === $property) {
                    $property = 'phone';
                }
                if ('birthCommune' === $property) {
                    $property = 'birthPlace';
                }
                if (1 === preg_match('#name|firstName#', $property)) {
                    $identityDto->fullName = sprintf('<b>%s</b>', $identityDto->$property);
                }
                
                $identityDto->$property = sprintf('<b>%s</b>', $identityDto->$property);
            }
        }
    }
}
