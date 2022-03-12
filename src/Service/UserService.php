<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\ViewModel\UserPresenter;
use App\ViewModel\UserViewModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserService
{
    public function __construct(
        private ParameterBagInterface $params,
        private SluggerInterface $slugger,
        private LicenceService $licenceService,
        private EntityManagerInterface $entityManager,
        private UserPresenter $userPresenter
    ) {
    }

    public function convertToUser(User $user): UserViewModel
    {
        $this->userPresenter->present($user);

        return $this->userPresenter->viewModel();
    }

    public function deleteUser(User $user): void
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
                if (!$data['entity']->{$method}()->isEmpty()) {
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
        if (!empty($users)) {
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
