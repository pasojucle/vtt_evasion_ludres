<?php

namespace App\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\DtoTransformer\UserCollectionDtoTransformer;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\UserPermission;
use App\Repository\IdentityRepository;
use App\Repository\LicenceRepository;
use App\Repository\UserPermissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\SecurityBundle\Security;

class UserStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly CollectionProvider $collectionProvider,
        private readonly UserCollectionDtoTransformer $transformer,
        private readonly LicenceRepository $licenceRepository,
        private readonly IdentityRepository $identityRepository,
        private readonly UserPermissionRepository $userPermissionRepository,
        private readonly Security $security,
    ) {
    }
    
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $users = [];
            $membersByUser = [];
            $licencesByUser = [];
            $permissionsByUser = [];
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
            foreach ($this->collectionProvider->provide($operation, $uriVariables, $context) as $userEntity) {
                $member = (array_key_exists($userEntity->getId(), $membersByUser)) ? $membersByUser[$userEntity->getId()] : null;
                $licences = (array_key_exists($userEntity->getId(), $licencesByUser)) ? $licencesByUser[$userEntity->getId()] : [];
                $permissions = (array_key_exists($userEntity->getId(), $permissionsByUser)) ? $permissionsByUser[$userEntity->getId()] : [];

                $users[] = $this->transformer->fromEntity($userEntity, $member, $licences, $permissions, $isGrantedUserList);
            }
            $this->sortByFullName($users);

            return new ArrayCollection($users);
        }

        return null;
    }

    private function sortByFullName(array &$users): void
    {
        usort($users, function ($a, $b) {
            return strtolower($a->fullName) < strtolower($b->fullName) ? -1 : 1;
        });
    }
}
