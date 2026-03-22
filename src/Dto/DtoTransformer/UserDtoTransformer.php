<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LicenceDto;
use App\Dto\UserDto;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Enum\PermissionEnum;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\Member;
use App\Entity\User;
use App\Repository\IdentityRepository;
use App\Repository\LicenceRepository;
use App\Service\ParameterService;
use App\Service\SeasonService;
use App\Service\UserService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserDtoTransformer
{
    public function __construct(
        private LicenceAgreementDtoTransformer $licenceAgreeementDtoTransformer,
        private IdentityDtoTransformer $identityDtoTransformer,
        private HealthDtoTransformer $healthDtoTransformer,
        private LevelDtoTransformer $levelDtoTransformer,
        private LicenceDtoTransformer $licenceDtoTransformer,
        private FFCTLicenceDtoTransformer $FFCTLicenceDtoTransformer,
        private AccessDecisionManagerInterface $accessDecisionManager,
        private TranslatorInterface $translator,
        private LicenceRepository $licenceRepository,
        private IdentityRepository $identityRepository,
        private SeasonService $seasonService,
        private ParameterService $parameterService,
        private UserService $userService, 
    ) {
    }

    public function fromEntity(User $user, ?array $histories = null): UserDto
    {
        $userDto = new UserDto();
        $mainIdentity = $user->getMainIdentity();
        $userDto->id = $user->getId();
        $userDto->licenceNumber = $user->getLicenceNumber();
        $userDto->member = $this->identityDtoTransformer->fromEntity($user->getIdentity());
        if ($user instanceof Member) {
            $userDto->legalGardian = ($user->getLegalGardian()) ? $this->identityDtoTransformer->fromEntity($user->getLegalGardian()) : null;
            $userDto->health = $this->healthDtoTransformer->fromEntity($user->getHealth());
            $userDto->boardRole = $user->getBoardRole()?->getName();
            $userDto->isBoardMember = null !== $user->getBoardRole();
            $userDto->secondKinship = ($user->getSecondContact()) ? $this->identityDtoTransformer->fromEntity($user->getSecondContact()) : null;
            $userDto->lastLicence = $this->getLastLicence($user, $histories);
            $userDto->prevLicence = $this->getPrevLicence($user);
            $userDto->permissions = $this->getPermissions($user);
            $userDto->trialSessionsPresent = $this->userService->trialSessionsPresent($user->getLastLicence(), $user);
            $userDto->ffctLicence = $this->FFCTLicenceDtoTransformer->fromEntity($userDto);
            $userDto->agreements = $this->licenceAgreeementDtoTransformer->fromEntities($user->getLastLicence()->getLicenceAgreements());
            $userDto->testingBikeRides = $this->testingBikeRides($userDto->lastLicence, $user->getSessions()->count());
            $userDto->isEndTesting = $this->userService->isEndTesting($user->getLastLicence(), $userDto->trialSessionsPresent);
            $userDto->mustProvideRegistration = $this->userService->mustProvideRegistration($user->getLastLicence(), $user->getLicences()->count());
            $userDto->canRenewRegistration = $this->canRenewRegistration($userDto->lastLicence);
        }
        $userDto->level = $this->levelDtoTransformer->fromEntity($user->getLevel());
        $userDto->mainEmail = $mainIdentity?->getEmail();
        $userDto->mainFullName = $mainIdentity?->getFullName();

        return $userDto;
    }

    public function getHeaderFromEntity(Member $user, ?array $histories = null, ?Identity $identity = null): UserDto
    {
        $userDto = new UserDto();
        if (!$identity) {
            $identity = $user->getIdentity();
        }
        $userDto->id = $user->getId();
        $userDto->licenceNumber = $user->getLicenceNumber();
        $userDto->member = $this->identityDtoTransformer->fromEntity($identity, $histories);
        $userDto->level = $this->levelDtoTransformer->fromEntity($user->getLevel());
        $userDto->lastLicence = $this->getLastLicence($user, $histories);
        $userDto->seasons = $this->getSeasons($user->getLicences());
        $userDto->testingBikeRides = $this->testingBikeRides($userDto->lastLicence, $user->getSessions()->count());
        $userDto->trialSessionsPresent = $this->userService->trialSessionsPresent($user->getLastLicence(), $user);
        $userDto->mustProvideRegistration = $this->userService->mustProvideRegistration($user->getLastLicence(), $user->getLicences()->count());
        $userDto->isEndTesting = $this-> userService->isEndTesting($user->getLastLicence(), $userDto->trialSessionsPresent);

        return $userDto;
    }

    public function getSessionHeaderFromEntity(Member $user, ?array $histories = null, ?Identity $identity = null): UserDto
    {
        $userDto = new UserDto();
        if (!$identity) {
            $identity = $user->getIdentity();
        }
        $userDto->id = $user->getId();
        $userDto->member = $this->identityDtoTransformer->headerFromEntity($identity, $histories);
        $userDto->level = $this->levelDtoTransformer->fromEntity($user->getLevel());
        $userDto->agreements = $this->licenceAgreeementDtoTransformer->fromEntities($user->getLastLicence()->getLicenceAgreements());
        $userDto->health = $this->healthDtoTransformer->fromEntity($user->getHealth());

        return $userDto;
    }

    public function fromEntities(Paginator|Collection|array $userEntities): array
    {
        $users = [];
        $members = [];
        /** @var Identity $identity */
        foreach ($this->identityRepository->findMembersByUsers($userEntities) as $identity) {
            $members[$identity->getUser()->getId()] = $identity;
        }
        foreach ($userEntities as $userEntity) {
            $user = (array_key_exists($userEntity->getId(), $members)) ? $members[$userEntity->getId()] : null;
            $users[] = $this->getHeaderFromEntity($userEntity, null, $user);
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

    public function identifiersFromEntity(User $userEntity): UserDto
    {
        $userDto = new UserDto();
        /** @var Identity $mainIdentity */
        $mainIdentity = $userEntity->getMainIdentity();
        
        $userDto->id = $userEntity->getId();
        $userDto->member = $this->identityDtoTransformer->headerFromEntity($userEntity->getIdentity());
        $userDto->mainEmail = $mainIdentity->getEmail();
        $userDto->mainFullName = $mainIdentity->getFullName();
        $userDto->licenceNumber = $userEntity->getLicenceNumber();
        if ($userEntity instanceof Member) {
            $userDto->lastLicence = $this->getLastLicence($userEntity, null);
            $userDto->trialSessionsPresent = $this->userService->trialSessionsPresent($userEntity->getLastLicence(), $userEntity);
            $userDto->isEndTesting = $this->userService->isEndTesting($userEntity->getLastLicence(), $userDto->trialSessionsPresent);
        }

        return $userDto;
    }

    private function testingBikeRides(LicenceDto $lastLicence, int $sessionsTotal): int
    {
        if (in_array($lastLicence->state['value'], [LicenceStateEnum::TRIAL_FILE_SUBMITTED, LicenceStateEnum::TRIAL_FILE_RECEIVED, LicenceStateEnum::TRIAL_COMPLETED])) {
            return $sessionsTotal;
        }

        return 0;
    }

    private function getLastLicence(Member $user, ?array $histories): LicenceDto
    {
        $licence = $user->getLastLicence();

        return $this->licenceDtoTransformer->fromEntity($licence, $histories);
    }

    private function getPrevLicence(Member $user): ?LicenceDto
    {
        if (!$user->getId()) {
            return null;
        }
        return $this->licenceDtoTransformer->fromEntity($this->licenceRepository->findOneByUserAndLastSeason($user));
    }

    private function getPermissions(Member $user): ?string
    {
        $token = new UsernamePasswordToken($user, 'none', $user->getRoles());
                            
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return 'Accès total au menu admin';
        }
        $permissions = [];
        /** @var PermissionEnum $permission */
        foreach ($user->getPermissions() as $permission) {
            $permissions[] = (PermissionEnum::BIKE_RIDE_CLUSTER === $permission)
                ? $permission->trans($this->translator)
                : sprintf('Accès à l\'admin pour gérer %s', $permission->trans($this->translator));
        }

        return (!empty($permissions)) ? implode('<br>', $permissions) : null;
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

    private function canRenewRegistration(LicenceDto $lastLicence): bool
    {
        return $this->parameterService->getParameterByName('NEW_SEASON_RE_REGISTRATION_ENABLED')
            && in_array($lastLicence->state['value'], [LicenceStateEnum::YEARLY_FILE_RECEIVED, LicenceStateEnum::YEARLY_FILE_REGISTRED])
            && $lastLicence->season === $this->seasonService->getSeasonForRenew();
    }
}
