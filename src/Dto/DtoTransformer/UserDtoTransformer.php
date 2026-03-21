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
use App\Repository\IdentityRepository;
use App\Repository\LicenceRepository;
use App\Repository\SessionRepository;
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

    public function fromEntity(Member $member, ?array $histories = null): UserDto
    {
        $userDto = new UserDto();
        $mainIdentity = $member->getMainIdentity();
        $userDto->id = $member->getId();
        $userDto->licenceNumber = $member->getLicenceNumber();
        $userDto->member = $this->identityDtoTransformer->fromEntity($member->getIdentity());
        $userDto->legalGardian = ($member->getLegalGardian()) ? $this->identityDtoTransformer->fromEntity($member->getLegalGardian()) : null;
        $userDto->secondKinship = ($member->getSecondContact()) ? $this->identityDtoTransformer->fromEntity($member->getSecondContact()) : null;
        $userDto->lastLicence = $this->getLastLicence($member, $histories);
        $userDto->prevLicence = $this->getPrevLicence($member);
        $userDto->health = $this->healthDtoTransformer->fromEntity($member->getHealth());
        $userDto->level = $this->levelDtoTransformer->fromEntity($member->getLevel());
        $userDto->mainEmail = $mainIdentity?->getEmail();
        $userDto->mainFullName = $mainIdentity?->getFullName();
        $userDto->boardRole = $member->getBoardRole()?->getName();
        $userDto->isBoardMember = null !== $member->getBoardRole();
        $userDto->ffctLicence = $this->FFCTLicenceDtoTransformer->fromEntity($userDto);
        $userDto->agreements = $this->licenceAgreeementDtoTransformer->fromEntities($member->getLastLicence()->getLicenceAgreements());
        $userDto->permissions = $this->getPermissions($member);

        $userDto->trialSessionsPresent = $this->userService->trialSessionsPresent($member->getLastLicence(), $member);
        $userDto->isEndTesting = $this->userService->isEndTesting($member->getLastLicence(), $userDto->trialSessionsPresent);
        $userDto->testingBikeRides = $this->testingBikeRides($userDto->lastLicence, $member->getSessions()->count());
        $userDto->mustProvideRegistration = $this->userService->mustProvideRegistration($member->getLastLicence(), $member->getLicences()->count());
        $userDto->canRenewRegistration = $this->canRenewRegistration($userDto->lastLicence);
         
        return $userDto;
    }

    public function getHeaderFromEntity(Member $member, ?array $histories = null, ?Identity $identity = null): UserDto
    {
        $userDto = new UserDto();
        if (!$identity) {
            $identity = $member->getIdentity();
        }
        $userDto->id = $member->getId();
        $userDto->licenceNumber = $member->getLicenceNumber();
        $userDto->member = $this->identityDtoTransformer->fromEntity($identity, $histories);
        $userDto->level = $this->levelDtoTransformer->fromEntity($member->getLevel());
        $userDto->lastLicence = $this->getLastLicence($member, $histories);
        $userDto->seasons = $this->getSeasons($member->getLicences());
        $userDto->testingBikeRides = $this->testingBikeRides($userDto->lastLicence, $member->getSessions()->count());
        $userDto->trialSessionsPresent = $this->userService->trialSessionsPresent($member->getLastLicence(), $member);
        $userDto->mustProvideRegistration = $this->userService->mustProvideRegistration($member->getLastLicence(), $member->getLicences()->count());
        $userDto->isEndTesting = $this-> userService->isEndTesting($member->getLastLicence(), $userDto->trialSessionsPresent);

        return $userDto;
    }

    public function getSessionHeaderFromEntity(Member $member, ?array $histories = null, ?Identity $identity = null): UserDto
    {
        $userDto = new UserDto();
        if (!$identity) {
            $identity = $member->getIdentity();
        }
        $userDto->id = $member->getId();
        $userDto->member = $this->identityDtoTransformer->headerFromEntity($identity, $histories);
        $userDto->level = $this->levelDtoTransformer->fromEntity($member->getLevel());
        $userDto->agreements = $this->licenceAgreeementDtoTransformer->fromEntities($member->getLastLicence()->getLicenceAgreements());
        $userDto->health = $this->healthDtoTransformer->fromEntity($member->getHealth());

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

    public function identifiersFromEntity(Member $userEntity): UserDto
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
        $userDto->trialSessionsPresent = $this->userService->trialSessionsPresent($userEntity->getLastLicence(), $userEntity);
        $userDto->isEndTesting = $this->userService->isEndTesting($userEntity->getLastLicence(), $userDto->trialSessionsPresent);
        return $userDto;
    }

    private function testingBikeRides(LicenceDto $lastLicence, int $sessionsTotal): int
    {
        if (in_array($lastLicence->state['value'], [LicenceStateEnum::TRIAL_FILE_SUBMITTED, LicenceStateEnum::TRIAL_FILE_RECEIVED, LicenceStateEnum::TRIAL_COMPLETED])) {
            return $sessionsTotal;
        }

        return 0;
    }

    private function getLastLicence(Member $member, ?array $histories): LicenceDto
    {
        $licence = $member->getLastLicence();

        return $this->licenceDtoTransformer->fromEntity($licence, $histories);
    }

    private function getPrevLicence(Member $member): ?LicenceDto
    {
        if (!$member->getId()) {
            return null;
        }
        return $this->licenceDtoTransformer->fromEntity($this->licenceRepository->findOneByUserAndLastSeason($member));
    }

    private function getPermissions(Member $member): ?string
    {
        $token = new UsernamePasswordToken($member, 'none', $member->getRoles());
                            
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return 'Accès total au menu admin';
        }
        $permissions = [];
        /** @var PermissionEnum $permission */
        foreach ($member->getPermissions() as $permission) {
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
