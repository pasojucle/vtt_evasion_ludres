<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\LicenceDto;
use App\Dto\UserDto;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Enum\PermissionEnum;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\User;
use App\Repository\IdentityRepository;
use App\Repository\LicenceRepository;
use App\Repository\SessionRepository;
use App\Service\ParameterService;
use App\Service\SeasonService;
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
        private SessionRepository $sessionRepository,
        private SeasonService $seasonService,
        private ParameterService $parameterService,
    ) {
    }

    public function fromEntity(User $user, ?array $histories = null): UserDto
    {
        $userDto = new UserDto();
        $mainIdentity = $user->getMainIdentity();
        $userDto->id = $user->getId();
        $userDto->licenceNumber = $user->getLicenceNumber();
        $userDto->member = $this->identityDtoTransformer->fromEntity($user->getIdentity());
        $userDto->legalGardian = ($user->getLegalGardian()) ? $this->identityDtoTransformer->fromEntity($user->getLegalGardian()) : null;
        $userDto->secondKinship = ($user->getSecondContact()) ? $this->identityDtoTransformer->fromEntity($user->getSecondContact()) : null;
        $userDto->lastLicence = $this->getLastLicence($user, $histories);
        $userDto->prevLicence = $this->getPrevLicence($user);
        $userDto->health = $this->healthDtoTransformer->fromEntity($user->getHealth());
        $userDto->level = $this->levelDtoTransformer->fromEntity($user->getLevel());
        $userDto->mainEmail = $mainIdentity?->getEmail();
        $userDto->mainFullName = $mainIdentity?->getFullName();
        $userDto->boardRole = $user->getBoardRole()?->getName();
        $userDto->isBoardMember = null !== $user->getBoardRole();
        $userDto->ffctLicence = $this->FFCTLicenceDtoTransformer->fromEntity($userDto);
        $userDto->agreements = $this->licenceAgreeementDtoTransformer->fromEntities($user->getLastLicence()->getLicenceAgreements());
        $userDto->permissions = $this->getPermissions($user);

        $userDto->trialSessionsPresent = $this->trialSessionsPresent($userDto->lastLicence, $user);
        $userDto->isEndTesting = $this->isEndTesting($userDto->lastLicence, $userDto->trialSessionsPresent);
        $userDto->testingBikeRides = $this->testingBikeRides($userDto->lastLicence, $user->getSessions()->count());
        $userDto->mustProvideRegistration = $this->mustProvideRegistration($userDto->lastLicence, $user->getLicences()->count());
        $userDto->canRenewRegistration = $this->canRenewRegistration($userDto->lastLicence);
         
        return $userDto;
    }

    public function getHeaderFromEntity(User $user, ?array $histories = null, ?Identity $member = null): UserDto
    {
        $userDto = new UserDto();
        if (!$member) {
            $member = $user->getIdentity();
        }
        $userDto->id = $user->getId();
        $userDto->licenceNumber = $user->getLicenceNumber();
        $userDto->member = $this->identityDtoTransformer->fromEntity($member, $histories);
        $userDto->level = $this->levelDtoTransformer->fromEntity($user->getLevel());
        $userDto->lastLicence = $this->getLastLicence($user, $histories);
        $userDto->seasons = $this->getSeasons($user->getLicences());
        $userDto->testingBikeRides = $this->testingBikeRides($userDto->lastLicence, $user->getSessions()->count());
        $userDto->trialSessionsPresent = $this->trialSessionsPresent($userDto->lastLicence, $user);
        $userDto->mustProvideRegistration = $this->mustProvideRegistration($userDto->lastLicence, $user->getLicences()->count());
        $userDto->isEndTesting = $this->isEndTesting($userDto->lastLicence, $userDto->trialSessionsPresent);

        return $userDto;
    }

    public function getSessionHeaderFromEntity(User $user, ?array $histories = null, ?Identity $member = null): UserDto
    {
        $userDto = new UserDto();
        if (!$member) {
            $member = $user->getIdentity();
        }
        $userDto->id = $user->getId();
        $userDto->member = $this->identityDtoTransformer->headerFromEntity($member, $histories);
        $userDto->level = $this->levelDtoTransformer->fromEntity($user->getLevel());
        $userDto->agreements = $this->licenceAgreeementDtoTransformer->fromEntities($user->getLastLicence()->getLicenceAgreements());
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
        $userDto->lastLicence = $this->getLastLicence($userEntity, null);
        $userDto->trialSessionsPresent = $this->trialSessionsPresent($userDto->lastLicence, $userEntity);
        $userDto->isEndTesting = $this->isEndTesting($userDto->lastLicence, $userDto->trialSessionsPresent);
        return $userDto;
    }
    
    private function isEndTesting(LicenceDto $lastLicence, int $sessionPresents): bool
    {
        if (in_array($lastLicence->state['value'], [LicenceStateEnum::TRIAL_FILE_SUBMITTED, LicenceStateEnum::TRIAL_FILE_RECEIVED, LicenceStateEnum::TRIAL_COMPLETED])) {
            return 2 < $sessionPresents;
        }

        return false;
    }

    private function testingBikeRides(LicenceDto $lastLicence, int $sessionsTotal): int
    {
        if (in_array($lastLicence->state['value'], [LicenceStateEnum::TRIAL_FILE_SUBMITTED, LicenceStateEnum::TRIAL_FILE_RECEIVED, LicenceStateEnum::TRIAL_COMPLETED])) {
            return $sessionsTotal;
        }

        return 0;
    }

    private function trialSessionsPresent(LicenceDto $lastLicence, User $user): int
    {
        if (in_array($lastLicence->state['value'], [LicenceStateEnum::TRIAL_FILE_SUBMITTED, LicenceStateEnum::TRIAL_FILE_RECEIVED, LicenceStateEnum::TRIAL_COMPLETED])) {
            return $this->sessionRepository->findParticipationByUser($user);
        }

        return 0;
    }

    private function mustProvideRegistration(LicenceDto $lastLicence, int $licencesTotal): bool
    {
        return 1 === $licencesTotal && $lastLicence->isSeasonLicence && LicenceStateEnum::YEARLY_FILE_SUBMITTED === $lastLicence->state['value'];
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

    private function getPermissions(User $user): ?string
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
