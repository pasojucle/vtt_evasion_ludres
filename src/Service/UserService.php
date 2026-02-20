<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Licence;
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
        private OrderLineRepository $orderLineRepository,
        private readonly LicenceService $licenceService,
    ) {
    }

    public function deleteUser(User $user): void
    {
        $allData = [
            [
                'entity' => $user,
                'methods' => ['getSessions', 'getLicences', 'getIdentity', 'getUserGardians', 'getOrderHeaders', 'getRespondents'],
            ],
        ];
        foreach ($allData as $data) {
            foreach ($data['methods'] as $method) {
                foreach ($data['entity']->{$method}() as $entity) {
                    if ($entity instanceof OrderHeader) {
                        $this->orderLineRepository->deleteByOrderHeader($entity);
                    }
                    if ($entity instanceof Licence) {
                        foreach ($entity->getLicenceAgreements() as $licenceAgreement) {
                            $entity->removeLicenceAgreement($licenceAgreement);
                            $this->entityManager->remove($licenceAgreement);
                        }
                    }
                    if ($entity) {
                        $this->entityManager->remove($entity);
                    }
                }
            }
        }
        $this->surveyResponseRepository->deleteResponsesByUser($user);

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    public function licenceIsActive(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        $lastLicence = $user->getLastLicence();
        return $this->licenceService->isActive($lastLicence);
    }
}
