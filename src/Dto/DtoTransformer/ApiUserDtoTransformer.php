<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Entity\User;
use App\Dto\ApiUserDto;
use App\Entity\Licence;
use App\Entity\Identity;
use App\Repository\LicenceRepository;
use App\Repository\IdentityRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class ApiUserDtoTransformer
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
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }


    public function listHeaderFromEntity(User $user, Identity $member): ApiUserDto
    {
        $userDto = new ApiUserDto();
        $userDto->id = $user->getId();
        $userDto->fullName = sprintf('%s %s', mb_strtoupper($member->getName()), mb_ucfirst($member->getFirstName()));
        $userDto->level = [
            'name' => $user->getLevel()?->getTitle() ?? '',
            'color' => $user->getLevel()?->getColor() ?? '#ffffff',
        ];
        $userDto->seasons = $this->getLicenceSeasons($user);
        $userDto->testingBikeRides = $this->testingBikeRides($user->getLastLicence(), $user->getSessions()->count());
        $userDto->boardMember = (null !== $user->getBoardRole()) ? '<i class="fa-solid fa-crown"></i>' : '';
        $userDto->btnShow = $this->urlGenerator->generate('admin_user', ['user' => $user->getId()]);
        // dump($userDto);
        return $userDto;
    }


    private function testingBikeRides(?Licence $lastLicence, int $sessionsTotal): string
    {
        if (false === $lastLicence?->isFinal()) {
            return sprintf('%s/3 séances d\'essai', $sessionsTotal);
        }

        return '';
    }

    public function listFromEntities(array $userEntities): array
    {
        $users = [];
        $members = [];
        /** @var Identity $member */
        foreach ($this->identityRepository->findMembersByUsers($userEntities) as $member) {
            $members[$member->getUser()->getId()] = $member;
        }
        foreach ($userEntities as $userEntity) {
            $member = (array_key_exists($userEntity->getId(), $members)) ? $members[$userEntity->getId()] : null;
            $users[] = $this->listHeaderFromEntity($userEntity, $member);
        }
        $this->sortByFullName($users);

        return $users;
    }

    private function sortByFullName(array &$users): void
    {
        usort($users, function ($a, $b) {
            return strtolower($a->fullName) < strtolower($b->fullName) ? -1 : 1;
        });
    }

    private function getLicenceSeasons(User $user): array
    {
        return $this->licenceRepository->findSeasons($user);
    } 
}
