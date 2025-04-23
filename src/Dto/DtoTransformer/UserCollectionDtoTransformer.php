<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\UserCollectionDto;
use App\Entity\Enum\PermissionEnum;
use App\Entity\Identity;
use App\Entity\Level;
use App\Entity\Licence;
use App\Entity\User;
use App\Entity\UserPermission;
use App\Repository\IdentityRepository;
use App\Repository\LicenceRepository;
use App\Repository\UserPermissionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserCollectionDtoTransformer
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
        private readonly TranslatorInterface $translator,
    ) {
    }


    public function fromEntity(User $user, Identity $member, array $licences, array $permissions, ?bool $isGrantedUserList): UserCollectionDto
    {
        $userDto = new UserCollectionDto();
        $userDto->id = $user->getId();
        $fullName = sprintf('%s %s', mb_strtoupper($member->getName()), mb_ucfirst($member->getFirstName()));
        $userDto->fullName = $fullName;
        $userDto->level = $this->getLevel($user);
        $userDto->permissions = $this->getPermissions($permissions);
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

    private function getPermissions(array $enumPermissions): array
    {
        $permissions = [];
        foreach ($enumPermissions as $permission) {
            $permissions[] = ['id' => $permission->value, 'name' => $permission->trans($this->translator)];
        }

        return $permissions;
    }
}
