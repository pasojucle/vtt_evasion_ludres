<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\IdentityDto;
use App\Dto\LicenceDto;
use App\Dto\UserDto;
use App\Entity\Enum\IdentityKindEnum;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\User;
use App\Repository\IdentityRepository;
use App\Repository\LicenceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserDtoTransformer
{
    public function __construct(
        private ApprovalDtoTransformer $approvalDtoTransformer,
        private IdentityDtoTransformer $identityDtoTransformer,
        private HealthDtoTransformer $healthDtoTransformer,
        private LevelDtoTransformer $levelDtoTransformer,
        private LicenceDtoTransformer $licenceDtoTransformer,
        private FFCTLicenceDtoTransformer $FFCTLicenceDtoTransformer,
        private AccessDecisionManagerInterface $accessDecisionManager,
        private TranslatorInterface $translator,
        private LicenceRepository $licenceRepository,
        private IdentityRepository $identityRepository,
    ) {
    }

    public function fromEntity(User $user, ?array $histories = null): UserDto
    {
        $identitiesByType = $this->identityDtoTransformer->fromEntities($user->getIdentities(), $histories);

        $userDto = new UserDto();

        $userDto->id = $user->getId();
        $userDto->licenceNumber = $user->getLicenceNumber();
        $userDto->member = (array_key_exists(IdentityKindEnum::MEMBER->name, $identitiesByType)) ? $identitiesByType[IdentityKindEnum::MEMBER->name] : null;
        $userDto->kinship = (array_key_exists(IdentityKindEnum::KINSHIP->name, $identitiesByType)) ? $identitiesByType[IdentityKindEnum::KINSHIP->name] : null;
        $userDto->secondKinship = (array_key_exists(IdentityKindEnum::SECOND_CONTACT->name, $identitiesByType)) ? $identitiesByType[IdentityKindEnum::SECOND_CONTACT->name] : null;
        $userDto->lastLicence = $this->getLastLicence($user, $histories);
        $userDto->prevLicence = $this->getPrevLicence($user);
        $userDto->hasAlreadyBeenRegistered = $this->hasAlreadyBeenRegistered($user);
        $userDto->health = $this->healthDtoTransformer->fromEntity($user->getHealth());
        $userDto->level = $this->levelDtoTransformer->fromEntity($user->getLevel());
        $userDto->mainEmail = $this->getMainEmail($identitiesByType, $userDto->lastLicence->category);
        $userDto->mainFullName = $this->getMainFullName($identitiesByType, $userDto->lastLicence->category);
        $userDto->boardRole = $user->getBoardRole()?->getName();
        $userDto->isBoardMember = null !== $user->getBoardRole();
        $userDto->ffctLicence = $this->FFCTLicenceDtoTransformer->fromEntity($userDto);
        $userDto->approvals = $this->approvalDtoTransformer->fromEntities($user->getApprovals());
        $userDto->permissions = $this->getPermissions($user);

        $sessionsTotal = $user->getSessions()->count();
        $userDto->isEndTesting = $this->isEndTesting($userDto->lastLicence, $sessionsTotal);
        $userDto->testingBikeRides = $this->testingBikeRides($userDto->lastLicence, $sessionsTotal);
        $userDto->mustProvideRegistration = $this->mustProvideRegistration($userDto->lastLicence, $user->getLicences()->count());

        return $userDto;
    }

    public function getHeaderFromEntity(User $user, ?array $histories = null, ?Identity $member = null): UserDto
    {
        $userDto = new UserDto();
        if (!$member) {
            $member = $this->identityRepository->findOneMemberByUser($user);
        }
        $userDto->id = $user->getId();
        $userDto->licenceNumber = $user->getLicenceNumber();
        $userDto->member = $this->identityDtoTransformer->fromEntity($member, $histories);
        $userDto->level = $this->levelDtoTransformer->fromEntity($user->getLevel());
        $userDto->lastLicence = $this->getLastLicence($user, $histories);
        $userDto->seasons = $this->getSeasons($user->getLicences());
        $sessionsTotal = $user->getSessions()->count();
        $userDto->testingBikeRides = $this->testingBikeRides($userDto->lastLicence, $sessionsTotal);

        return $userDto;
    }

    public function getSessionHeaderFromEntity(User $user, ?array $histories = null, ?Identity $member = null): UserDto
    {
        $userDto = new UserDto();
        if (!$member) {
            $member = $this->identityRepository->findOneMemberByUser($user);
        }
        $userDto->id = $user->getId();
        $userDto->member = $this->identityDtoTransformer->headerFromEntity($member, $histories);
        $userDto->level = $this->levelDtoTransformer->fromEntity($user->getLevel());
        $userDto->approvals = $this->approvalDtoTransformer->fromEntities($user->getApprovals());
        $userDto->health = $this->healthDtoTransformer->fromEntity($user->getHealth());

        return $userDto;
    }

    public function fromEntities(Paginator|Collection|array $userEntities): array
    {
        $users = [];
        $members = [];
        /** @var Identity $member */
        foreach ($this->identityRepository->findMembersByUsers($userEntities) as $member) {
            $members[$member->getUser()->getId()] = $member;
        }
        foreach ($userEntities as $userEntity) {
            $member = (array_key_exists($userEntity->getId(), $members)) ? $members[$userEntity->getId()] : null;
            $users[] = $this->getHeaderFromEntity($userEntity, null, $member);
        }

        $this->sortByFullName($users);

        return $users;
    }

    private function sortByFullName(array &$users): void
    {
        uasort($users, function ($a, $b) {
            return strtolower($a->member->fullName) < strtolower($b->member->fullName) ? -1 : 1;
        });
    }

    private function getMainEmail(array $identitiesByType, int $category): ?string
    {
        if (!empty($identitiesByType)) {
            $identity = (Licence::CATEGORY_MINOR === $category && array_key_exists(IdentityKindEnum::KINSHIP->name, $identitiesByType))
                ? $identitiesByType[IdentityKindEnum::KINSHIP->name]
                : $identitiesByType[IdentityKindEnum::MEMBER->name];
            return $identity?->email;
        }

        return '';
    }

    private function getMainFullName(array $identitiesByType, int $category): ?string
    {
        if (!empty($identitiesByType)) {
            $identity = (Licence::CATEGORY_MINOR === $category && array_key_exists(IdentityKindEnum::KINSHIP->name, $identitiesByType))
                ? $identitiesByType[IdentityKindEnum::KINSHIP->name]
                : $identitiesByType[IdentityKindEnum::MEMBER->name];
            return $identity?->fullName;
        }

        return '';
    }

    private function getMainIdentity(User $userEntity): ?IdentityDto
    {
        $identitiesByType = $this->identityDtoTransformer->fromEntities($userEntity->getIdentities());
        $lastLicence = $this->getLastLicence($userEntity, null);
        if (!empty($identitiesByType)) {
            return (Licence::CATEGORY_MINOR === $lastLicence->category && array_key_exists(IdentityKindEnum::KINSHIP->name, $identitiesByType))
            ? $identitiesByType[IdentityKindEnum::KINSHIP->name]
            : $identitiesByType[IdentityKindEnum::MEMBER->name];
        }

        return null;
    }

    public function identifiersFromEntity(User $userEntity): UserDto
    {
        $userDto = new UserDto();
        /** @var IdentityDto $mainIdentity */
        $mainIdentity = $this->getMainIdentity($userEntity);
        
        $userDto->member = $this->identityDtoTransformer->headerFromEntity($this->identityRepository->findOneMemberByUser($userEntity));
        $userDto->mainEmail = $mainIdentity->email;
        $userDto->mainFullName = $mainIdentity->fullName;
        $userDto->licenceNumber = $userEntity->getLicenceNumber();
        return $userDto;
    }
    
    private function isEndTesting(LicenceDto $lastLicence, int $sessionTotal): bool
    {
        if (false === $lastLicence->isFinal) {
            return 2 < $sessionTotal;
        }

        return false;
    }

    private function testingBikeRides(LicenceDto $lastLicence, int $sessionsTotal): ?int
    {
        if (false === $lastLicence->isFinal) {
            return $sessionsTotal;
        }

        return null;
    }

    private function mustProvideRegistration(LicenceDto $lastLicence, int $licencesTotal): bool
    {
        return 1 === $licencesTotal && $lastLicence->isSeasonLicence && $lastLicence->isFinal && Licence::STATUS_WAITING_VALIDATE === $lastLicence->status;
    }

    private function getLastLicence(User $user, ?array $histories): LicenceDto
    {
        $licence = $user->getLastLicence();

        return $this->licenceDtoTransformer->fromEntity($licence, $histories);
    }

    private function getPrevLicence(User $user): ?LicenceDto
    {
        if (!$user->getId()) {
            return null;
        }
        return $this->licenceDtoTransformer->fromEntity($this->licenceRepository->findOneByUserAndLastSeason($user));
    }

    private function hasAlreadyBeenRegistered(User $user): bool
    {
        if (!$user->getId()) {
            return false;
        }
        return !empty($this->licenceRepository->findByUserAndPeriod($user, 5));
    }

    private function getPermissions(User $user): ?string
    {
        $token = new UsernamePasswordToken($user, 'none', $user->getRoles());
                            
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return 'Accès total au menu admin';
        }
        $permissions = [];
        foreach ($user->getPermissions() as $name => $value) {
            if ($value) {
                $permissions[] = sprintf('Accès à l\'admin pour gérer %s', $this->translator->trans(sprintf('permission.%s', strtolower($name))));
            }
        }

        return (!empty($permissions)) ? implode('<br>', $permissions) : null;
    }

    public function mainEmailFromEntity(User $userEntity): string
    {
        $identitiesByType = $this->identityDtoTransformer->fromEntities($userEntity->getIdentities());
        $lastLicence = $this->getLastLicence($userEntity, null);
        return $this->getMainEmail($identitiesByType, $lastLicence->category);
    }

    public function getSeasons(?Collection $licences): string
    {
        $seasons = [];
        /** @var Licence $licence */
        foreach ($licences as $licence) {
            $seasons[] = $licence->getSeason();
        }

        return implode(' - ', $seasons);
    }
}
