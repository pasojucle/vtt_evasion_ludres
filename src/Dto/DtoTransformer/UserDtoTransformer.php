<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use DateTime;
use App\Dto\UserDto;
use App\Entity\User;
use App\Entity\Level;
use App\Dto\LicenceDto;
use App\Entity\Licence;
use App\Entity\Identity;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class UserDtoTransformer
{
    public function __construct(
        private ApprovalDtoTransformer $approvalDtoTransformer,
        private IdentityDtoTransformer $identityDtoTransformer,
        private HealthDtoTransformer $healthDtoTransformer,
        private LevelDtoTransformer $levelDtoTransformer,
        private LicenceDtoTransformer $licenceDtoTransformer,
        private FFCTLicenceDtoTransformer $fFCTLicenceDtoTransformer,
        private RoleHierarchyInterface $roleHierarchy
    )
    {
        
    }

    public function fromEntity(User $user, ?array $changes = null): UserDto
    {
        $identitiesByType = $this->identityDtoTransformer->fromEntities($user->getIdentities(), $changes);

        $userDto = new UserDto;
        $userDto->id = $user->getId();
        $userDto->licenceNumber = $user->getLicenceNumber();
        $userDto->member = (array_key_exists(Identity::TYPE_MEMBER, $identitiesByType)) ? $identitiesByType[Identity::TYPE_MEMBER] : null;
        $userDto->kinship = (array_key_exists(Identity::TYPE_KINSHIP, $identitiesByType)) ? $identitiesByType[Identity::TYPE_KINSHIP] : null;
        $userDto->secondKinship = (array_key_exists(Identity::TYPE_SECOND_CONTACT, $identitiesByType)) ? $identitiesByType[Identity::TYPE_SECOND_CONTACT] : null;
        
        $userDto->lastLicence = $this->getLastLicence($user, $changes);
        $userDto->seasonLicence = ($userDto->lastLicence->isSeasonLicence) ? $userDto->lastLicence : null;

        $userDto->health = $this->healthDtoTransformer->fromEntity($user->getHealth());
        $userDto->level = $this->levelDtoTransformer->fromEntity($user->getLevel());
        $userDto->mainEmail = $this->getMainEmail($identitiesByType, $userDto->lastLicence->category);
        $userDto->boardRole = $user->getBoardRole()?->getName();
        $userDto->approvals = $this->approvalDtoTransformer->fromEntities($user->getApprovals());
        $userDto->isBoardMember = null !== $user->getBoardRole();
        $userDto->ffctLicence = $this->fFCTLicenceDtoTransformer->fromEntity($userDto);
        $userDto->approvals = $this->approvalDtoTransformer->fromEntities($user->getApprovals(), $changes);
        $userDto->accessControl = $this->getAccessControl($user->getRoles());

        $sessionsTotal = $user->getSessions()->count();
        $userDto->isEndTesting = $this->isEndTesting($userDto->lastLicence, $sessionsTotal);
        $userDto->testingBikeRides = $this->testingBikeRides($userDto->lastLicence, $sessionsTotal);
        $userDto->mustProvideRegistration = $this->mustProvideRegistration($userDto->lastLicence, $user->getLicences()->count());

        return $userDto;
    }

    public function fromEntities(Paginator|Collection|array $userEntities): array
    {
        $users = [];
        foreach($userEntities as $userEntity) {
            $users[] = $this->fromEntity($userEntity);
        }

        return $users;
    }

    public function getMainEmail(array $identitiesByType, int $category): ?string
    {
        if (!empty($identitiesByType)) {
            $identity = (Licence::CATEGORY_MINOR === $category) ? $identitiesByType[Identity::TYPE_KINSHIP] : $identitiesByType[Identity::TYPE_MEMBER];
            return $identity?->email;
        }

        return '';
    }



    public function isMember(?Level $level): bool
    {

        return Level::TYPE_SCHOOL_MEMBER === $level?->getType();
    }

    public function isFramer(?Level $level): bool
    {

        return Level::TYPE_FRAME === $level?->getType();
    }

    public function isEndTesting(LicenceDto $lastLicence, int $sessionTotal): bool
    {
        if (false === $lastLicence->isFinal) {
            return 2 < $sessionTotal;
        }

        return false;
    }

    public function testingBikeRides(LicenceDto $lastLicence, int $sessionsTotal): ?int
    {
        if (false === $lastLicence->isFinal) {
            return $sessionsTotal;
        }

        return null;
    }

    public function mustProvideRegistration(LicenceDto $lastLicence, int $licencesTotal): bool
    {
        return 1 === $licencesTotal && $lastLicence->isSeasonLicence && $lastLicence->isFinal && Licence::STATUS_WAITING_VALIDATE === $lastLicence->status;
    }

    // private function getSeasonLicence(User $user, bool $isNewMember): ?LicenceDto
    // {
    //     $season = $this->services->currentSeason;

    //     foreach ($user->getLicences() as $licence) {
    //         if ($season === $licence->getSeason()) {
    //             return LicenceDto::fromLicence($licence, $isNewMember, $this->services);
    //         }
    //     }

    //     return null;
    // }

    private function getLastLicence(User $user, ?array $changes): LicenceDto
    {
        $licence = $user->getLastLicence();

        return $this->licenceDtoTransformer->fromEntity($licence, $changes);
    }

    public function getAccessControl(array $roles): ?string
    {
        $reachableRoles = $this->roleHierarchy->getReachableRoleNames($roles);

        return match (true) {
            in_array('ROLE_ADMIN', $reachableRoles) => 'Accès total au menu admin',
            in_array('ROLE_REGISTER', $reachableRoles) => 'Accès à l\'admin pour gérere les inscriptions',
            in_array('ROLE_FRAME', $reachableRoles) => 'Accès à l\'admin pour les sorties',
            default => null,
        };
    }


    public function getFFCTLicence(string $licenceNumber): string
    {
        if (1 === preg_match('#^\d+$#', $licenceNumber)) {
            return sprintf('Licence n° %s', $licenceNumber);
        }

        return '';
    }
}