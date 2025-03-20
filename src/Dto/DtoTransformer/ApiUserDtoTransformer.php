<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Entity\User;
use App\Entity\Level;
use App\Dto\ApiUserDto;
use App\Entity\Licence;
use App\Entity\Identity;
use App\Repository\LicenceRepository;
use App\Repository\IdentityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class ApiUserDtoTransformer
{
    public function __construct(
        private readonly ApprovalDtoTransformer $approvalDtoTransformer,
        private readonly IdentityDtoTransformer $identityDtoTransformer,
        private readonly HealthDtoTransformer $healthDtoTransformer,
        private readonly LevelDtoTransformer $levelDtoTransformer,
        private readonly LicenceDtoTransformer $licenceDtoTransformer,
        private readonly FFCTLicenceDtoTransformer $FFCTLicenceDtoTransformer,
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly TranslatorInterface $translator,
        private readonly LicenceRepository $licenceRepository,
        private readonly IdentityRepository $identityRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
    }


    public function listHeaderFromEntity(User $user, Identity $member): ApiUserDto
    {
        $userDto = new ApiUserDto();
        $userDto->id = $user->getId();
        $fullName = sprintf('%s %s', mb_strtoupper($member->getName()), mb_ucfirst($member->getFirstName()));
        $userDto->fullName = $fullName;
        $userDto->level = [
            'name' => $user->getLevel()?->getTitle() ?? '',
            'color' => $user->getLevel()?->getColor() ?? '#ffffff',
        ];
        $userDto->seasons = $this->getLicenceSeasons($user);
        $userDto->testingBikeRides = $this->testingBikeRides($user->getLastLicence(), $user->getSessions()->count());
        $userDto->boardMember = (null !== $user->getBoardRole()) ? '<i class="fa-solid fa-crown"></i>' : '';
        $userDto->btnShow = $this->urlGenerator->generate('admin_user', ['user' => $user->getId()]);
        $userDto->actions = $this->getActions($user, $fullName);
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

    private function getActions(User $user, string $fullName): array
    {
        $actions = [];
        if ($this->security->isGranted('USER_LIST') && Level::TYPE_SCHOOL_MEMBER === $user->getLevel()?->getType()) {
            $actions[] = [
                'path' => $this->urlGenerator->generate('admin_user_skill_edit', ['user' => $user->getId()]),
                'icon' => 'fa-solid fa-graduation-cap',
                'label' => 'Compétences',
            ];
        }
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $actions[] = [
                'path' => $this->urlGenerator->generate('admin_user_participation', ['user' => $user->getId()]),
                'icon' => 'fas fa-chart-line',
                'label' => 'Participation',
            ];
            $actions[] = [
                'path' => $this->urlGenerator->generate('admin_user_certificate', ['user' => $user->getId()]),
                'icon' => 'fas fa-file-contract',
                'label' => 'Attestation d\'inscription CE',
            ];
            if ($user->getLevel()?->isAccompanyingCertificat()) {
                $actions[] = [
                    'path' => $this->urlGenerator->generate('admin_user_accompanying_certificate', ['user' => $user->getId()]),
                    'icon' => 'fas fa-file-contract',
                    'label' => 'Attestation adulte accompagnateur',
                ];
            }
        }
        if ($this->security->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $actions[] = [
                'path' => $this->urlGenerator->generate('home', ['_switch_user'=> $user->getLicenceNumber()]),
                'icon' => 'fas fa-exchange-alt',
                'label' => 'Se connecter en tant que',
            ];
        }

        return ['title' => $fullName, 'items' => $actions];
    }
}

