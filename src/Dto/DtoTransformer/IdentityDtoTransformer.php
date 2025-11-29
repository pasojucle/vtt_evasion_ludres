<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\IdentityDto;
use App\Entity\Enum\IdentityKindEnum;
use App\Entity\Identity;
use App\Repository\HistoryRepository;
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
        private HistoryRepository $historyRepository,
        private SeasonService $seasonService,
    ) {
    }

    public function fromEntity(Identity $identity, ?array $histories = null): IdentityDto
    {
        $identityDto = new IdentityDto();

        $bithDate = $identity->getBirthDate();
        $identityDto->id = $identity->getId();
        if ($identity->getName() && $identity->getFirstName()) {
            $identityDto->name = mb_strtoupper($identity->getName());
            $identityDto->firstName = mb_ucfirst($identity->getFirstName());
            $identityDto->fullName = sprintf('%s %s', mb_strtoupper($identity->getName()), mb_ucfirst($identity->getFirstName()));
        }
        $identityDto->birthDate = ($bithDate) ? $bithDate->format('j/n/Y') : null;
        list($identityDto->birthPlace, $identityDto->birthDepartment, $identityDto->birthCountry) = $this->getBirthplace($identity);
        $identityDto->address = ($identity->getAddress()) ? $this->addressDtoTransformer->fromEntity($identity->getAddress(), $histories) : null;
        $identityDto->email = $identity->getEmail();
        $identityDto->phone = implode(' - ', array_filter([$identity->getMobile(), $identity->getPhone()]));
        $identityDto->emergencyPhone = $identity->getEmergencyPhone();
        $identityDto->emergencyContact = $identity->getEmergencyContact();
        $identityDto->phonesAnchor = $this->getPhonesAnchor($identity);
        $identityDto->picture = $this->getPicture($identity->getPicture());
        $identityDto->type = (null !== $identity->getKinShip()) ? Identity::KINSHIPS[$identity->getKinShip()] : null;

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


    public function fromEntities(Collection $identityEntities, ?array $histories = null): array
    {
        $identities = [];
        /** @var Identity $identity */
        foreach ($identityEntities as $identity) {
            $identities[$identity->getKind()->name] = $this->fromEntity($identity, $histories);
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
        $kinships = [IdentityKindEnum::KINSHIP->name, IdentityKindEnum::SECOND_CONTACT->name];
        $memberAddress = $identities[IdentityKindEnum::MEMBER->name]->address;
        foreach ($kinships as $kinship) {
            if (array_key_exists($kinship, $identities) && null === $identities[$kinship]->address) {
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
