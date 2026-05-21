<?php

declare(strict_types=1);

namespace App\State\Member\Processor;

use App\Entity\Member;
use App\Repository\OrderLineRepository;
use App\Repository\SurveyResponseRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;

class MemberDeleteProcessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderLineRepository $orderLineRepository,
        private SurveyResponseRepository $surveyResponseRepository,
        private UserService $userService,
    ) {}

    public function process(Member $member): string
    {
        $allData = [
            [
                'entity' => $member,
                'methods' => ['getSessions', 'getLicences', 'getIdentity', 'getmemberGardians', 'getOrderHeaders', 'getRespondents'],
            ],
        ];
        $fullname = $this->userService->getFullname($member);

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
        $this->surveyResponseRepository->deleteResponsesByUser($member);

        $this->entityManager->remove($member);
        $this->entityManager->flush();

        return $fullname;
    }
}