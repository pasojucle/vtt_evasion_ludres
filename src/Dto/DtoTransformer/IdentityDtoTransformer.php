<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\IdentityDto;
use App\Entity\Identity;
use App\Entity\UserGardian;
use App\Service\ProjectDirService;
use DateTime;
use DateTimeInterface;

class IdentityDtoTransformer
{
    public function __construct(
        private AddressDtoTransformer $addressDtoTransformer,
        private ProjectDirService $projectDirService,
    ) {
    }

    private function normamize(Identity|UserGardian $entity): array
    {
        if ($entity instanceof UserGardian) {
            return[
                $entity->getIdentity(),
                $entity->getKind(),
                $entity->getIdentity()->getAddress() ?? $entity->getUser()->getIdentity()->getAddress()
            ];
        }
        return[
            $entity,
            null,
            $entity->getAddress()
        ];
    }

    public function fromEntity(Identity|UserGardian $entity, ?array $histories = null): IdentityDto
    {
        $identityDto = new IdentityDto();
        [$identity, $kind, $address] = $this->normamize($entity);
        $bithDate = $identity->getBirthDate();
        $identityDto->id = $identity->getId();
        if ($identity->getName() && $identity->getFirstName()) {
            $identityDto->name = mb_strtoupper($identity->getName());
            $identityDto->firstName = mb_ucfirst($identity->getFirstName());
            $identityDto->fullName = $identity->getFullName();
        }
        $identityDto->kind = $kind;
        $identityDto->birthDate = ($bithDate) ? $bithDate->format('j/n/Y') : null;
        list($identityDto->birthPlace, $identityDto->birthDepartment, $identityDto->birthCountry) = $this->getBirthplace($identity);
        $identityDto->address = $this->addressDtoTransformer->fromEntity($address, $histories);
        $identityDto->email = $identity->getEmail();
        $identityDto->phone = implode(' - ', array_filter([$identity->getMobile(), $identity->getPhone()]));
        $identityDto->emergencyPhone = $identity->getEmergencyPhone();
        $identityDto->emergencyPhoneAnchor = $this->getPhoneAnchor($identity->getEmergencyPhone());
        $identityDto->emergencyContact = $identity->getEmergencyContact();
        $identityDto->phonesAnchor = $this->getPhonesAnchor($identity);
        $identityDto->picture = $this->getPicture($identity->getPicture());

        $identityDto->age = $this->getAge($bithDate);

        if ($histories) {
            $this->getDecoratedChanges($histories, $identityDto);
        }

        return $identityDto;
    }


    public function headerFromEntity(Identity $identity, ?array $histories = null): IdentityDto
    {
        $identityDto = new IdentityDto();

        $identityDto->id = $identity->getId();
        $identityDto->name = $identity->getName();
        $identityDto->firstName = $identity->getFirstName();
        $identityDto->fullName = $identity->getName() . ' ' . $identity->getFirstName();
        $identityDto->phone = implode(' - ', array_filter([$identity->getMobile(), $identity->getPhone()]));

        return $identityDto;
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

    private function getBirthplace(Identity $identity): array
    {
        $birthCommune = $identity->getBirthCommune();
        if ($birthCommune) {
            return [
                $birthCommune->getName(),
                sprintf('%s - %s', $birthCommune->getDepartment()->getId(), $birthCommune->getDepartment()->getName()),
                'France'];
        }

        return [$identity->getBirthPlace(), null, $identity->getBirthCountry()];
    }

    private function getDecoratedChanges(array $histories, IdentityDto &$identityDto): void
    {
        if (array_key_exists('Identity', $histories) && array_key_exists($identityDto->id, $histories['Identity'])) {
            $properties = array_keys($histories['Identity'][$identityDto->id]->getValue());
            
            foreach ($properties as $property) {
                if ('mobile' === $property) {
                    $property = 'phone';
                }
                if ('birthCommune' === $property) {
                    $property = 'birthPlace';
                }
                if (1 === preg_match('#name|firstName#', $property)) {
                    $identityDto->fullName = sprintf('<ins style="background-color:#ccffcc">%s</ins>', $identityDto->fullName);
                }
                if ('address' === $property) {
                    continue;
                }
                $identityDto->$property = sprintf('<ins style="background-color:#ccffcc">%s</ins>', $identityDto->$property);
            }
        }
    }
}
