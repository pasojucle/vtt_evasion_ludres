<?php

declare(strict_types=1);

namespace App\Service;

use App\DataTransferObject\User;
use App\Entity\User as EntityUser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserService
{
    private ParameterBagInterface $params;

    private SluggerInterface $slugger;

    private LicenceService $licenceService;

    private EntityManagerInterface $entityManager;

    public function __construct(ParameterBagInterface $params, SluggerInterface $slugger, LicenceService $licenceService, EntityManagerInterface $entityManager)
    {
        $this->params = $params;
        $this->slugger = $slugger;
        $this->licenceService = $licenceService;
        $this->entityManager = $entityManager;
    }

    public function convertPaginatorToUsers(Paginator $users): array
    {
        return $this->convertUsers($users);
    }

    public function convertArrayToUsers(array $users): array
    {
        return $this->convertUsers($users);
    }

    public function convertToUser(EntityUser $user): User
    {
        return $usersDto[] = new User(
            $user,
            $this->licenceService->getCurrentSeason(),
            $this->licenceService->getSeasonsStatus()
        );
    }

    public function deleteUser(EntityUser $user): void
    {
        $allData = [
            [
                'entity' => $user->getHealth(),
                'methods' => ['getDiseases', 'getHealthQuestions'],
            ],
            [
                'entity' => $user,
                'methods' => ['getSessions', 'getLicences', 'getApprovals', 'getIdentities'],
            ],
        ];
        foreach ($allData as $data) {
            foreach ($data['methods'] as $method) {
                if (! $data['entity']->{$method}()->isEmpty()) {
                    foreach ($data['entity']->{$method}() as $entity) {
                        $this->entityManager->remove($entity);
                    }
                }
            }
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    private function convertUsers($users): array
    {
        $usersDto = [];
        if (! empty($users)) {
            foreach ($users as $user) {
                $usersDto[] = new User(
                    $user,
                    $this->licenceService->getCurrentSeason(),
                    $this->licenceService->getSeasonsStatus()
                );
            }
        }

        return $usersDto;
    }
}
