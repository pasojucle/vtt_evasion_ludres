<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Entity\User;
use App\Entity\Level;
use App\Dto\ApiUserDto;
use App\Entity\Licence;
use App\Entity\Identity;
use App\Entity\UserPermission;
use App\Repository\LicenceRepository;
use App\Repository\IdentityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\UserPermissionRepository;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApiUserDtoTransformer
{
    public function __construct(
        private readonly LicenceRepository $licenceRepository,
        private readonly IdentityRepository $identityRepository,
        private readonly UserPermissionRepository $userPermissionRepository,
        private readonly UserRepository $userRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
    }


    public function listHeaderFromEntity(User $user, Identity $member, array $licences, array $permissions, ?bool $isGrantedUserList): ApiUserDto
    {
        $userDto = new ApiUserDto();
        $userDto->id = $user->getId();
        $fullName = sprintf('%s %s', mb_strtoupper($member->getName()), mb_ucfirst($member->getFirstName()));
        $userDto->fullName = $fullName;
        $userDto->level = $this->getLevel($user);
        $userDto->permissions = $permissions;
        $userDto->seasons = array_keys($licences);
        $userDto->testingBikeRides = $this->testingBikeRides($licences, $user->getSessions()->count());
        $userDto->boardMember = (null !== $user->getBoardRole()) ? '<i class="fa-solid fa-crown"></i>' : '';
        $userDto->btnShow = $this->urlGenerator->generate('admin_user', ['user' => $user->getId()]);
        $userDto->actions = $this->getActions($user, $fullName, $isGrantedUserList);
        $userDto->isBoardMember = (bool) $user->getBoardRole();

        return $userDto;
    }


    private function testingBikeRides(array $licences, int $sessionsTotal): string
    {
        $lastLicence = (!empty($licences)) ? $licences[array_key_last($licences)] : null;

        if (false === $lastLicence?->isFinal()) {
            return sprintf('%s/3 séances d\'essai', $sessionsTotal);
        }

        return '';
    }

    public function listAll(): array
    {
        $users = [];
        $membersByUser = [];
        $licencesByUser = [];
        $permissionsByUser = [];
        $userEntities = $this->userRepository->findAllAsc();
        $isGrantedUserList = $this->security->isGranted('USER_LIST');
        /** @var Identity $member */
        foreach ($this->identityRepository->findMembers() as $member) {
            $membersByUser[$member->getUser()->getId()] = $member;
        }
        /** @var Licence $licence */
        foreach ($this->licenceRepository->findAll() as $licence) {
            $licencesByUser[$licence->getUser()->getId()][$licence->getSeason()] = $licence;
        }
        /** @var UserPermission $userPermission */
        foreach ($this->userPermissionRepository->findAll() as $userPermission) {
            $permissionsByUser[$userPermission->getUser()->getId()][] = $userPermission->getPermission();
        }
        foreach ($userEntities as $userEntity) {
            $member = (array_key_exists($userEntity->getId(), $membersByUser)) ? $membersByUser[$userEntity->getId()] : null;
            $licences = (array_key_exists($userEntity->getId(), $licencesByUser)) ? $licencesByUser[$userEntity->getId()] : [];
            $permissions = (array_key_exists($userEntity->getId(), $permissionsByUser)) ? $permissionsByUser[$userEntity->getId()] : [];

            $users[] = $this->listHeaderFromEntity($userEntity, $member, $licences, $permissions, $isGrantedUserList);
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

    private function getLevel(User $user): array
    {
        return [
            'id' => $user->getLevel()?->getId(),
            'name' => $user->getLevel()?->getTitle() ?? '',
            'color' => $user->getLevel()?->getColor() ?? '#ffffff',
            'type' => $user->getLevel()?->getType(),
        ];
    }

    private function getActions(User $user, string $fullName, ?bool $isGrantedUserList): array
    {
        $actions = [];
        if (null === $isGrantedUserList) {
            $isGrantedUserList = $this->security->isGranted('USER_LIST');
        }
        if ($isGrantedUserList && Level::TYPE_SCHOOL_MEMBER === $user->getLevel()?->getType()) {
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
                'path' => $this->urlGenerator->generate('home', ['_switch_user' => $user->getLicenceNumber()]),
                'icon' => 'fas fa-exchange-alt',
                'label' => 'Se connecter en tant que',
            ];
        }

        return ['title' => $fullName, 'items' => $actions];
    }
}
