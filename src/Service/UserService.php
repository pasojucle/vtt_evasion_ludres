<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\OrderHeader;
use App\Entity\User;
use App\Repository\OrderLineRepository;
use App\Repository\SurveyResponseRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SurveyResponseRepository $surveyResponseRepository,
        private OrderLineRepository $orderLineRepository
    ) {
    }

    public function deleteUser(User $user): void
    {
        $allData = [
            [
                'entity' => $user,
                'methods' => ['getSessions', 'getLicences', 'getApprovals', 'getIdentities', 'getOrderHeaders', 'getRespondents', 'getSurveys'],
            ],
        ];
        foreach ($allData as $data) {
            foreach ($data['methods'] as $method) {
                if (!$data['entity']->{$method}()->isEmpty()) {
                    foreach ($data['entity']->{$method}() as $entity) {
                        if ($entity instanceof OrderHeader) {
                            $this->orderLineRepository->deleteByOrderHeader($entity);
                        }
                        $this->entityManager->remove($entity);
                    }
                }
            }
        }
        $this->surveyResponseRepository->deleteResponsesByUser($user);

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
