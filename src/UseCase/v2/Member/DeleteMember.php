<?php

declare(strict_types=1);

namespace App\UseCase\v2\Member;

use App\Entity\Licence;
use App\Entity\Member;
use App\Entity\OrderHeader;
use App\Repository\Interface\MemberRepositoryInterface;
use App\Repository\OrderLineRepository;
use App\Repository\SurveyResponseRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeleteMember
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderLineRepository $orderLineRepository,
        private SurveyResponseRepository $surveyResponseRepository,
        private MemberRepositoryInterface $memberRepository,
    ) {
    }
    public function __invoke(Member $member): void
    {
        // TODO Revoir pour anonymiser pour conserver l'historique des licences, sessions, les commandes, les réponses au sondage
        $this->removeRelations($member);
        $this->memberRepository->remove($member);
    }


    private function removeRelations(Member $member): void
    {
        $allData = [
            [
                'entity' => $member,
                'methods' => ['getSessions', 'getLicences', 'getIdentity', 'getmemberGardians', 'getOrderHeaders', 'getRespondents'],
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
        $this->surveyResponseRepository->deleteResponsesByMember($member);
    }
}
