<?php

declare(strict_types=1);

namespace App\State\Member\Processor;

use App\Dto\State\RedirectProcessorResult;
use App\Entity\Licence;
use App\Entity\Member;
use App\Entity\OrderHeader;
use App\Repository\OrderLineRepository;
use App\Repository\SurveyResponseRepository;
use App\State\Interface\FormRedirectProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class MemberDeleteProcessor implements FormRedirectProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderLineRepository $orderLineRepository,
        private SurveyResponseRepository $surveyResponseRepository,
    ) {
    }

    /**
     * @implements FormRedirectProcessorInterface<Member>
     */
    public function process(object $entity, ?array $uploadFiles, ?string $targetUrl = null): RedirectProcessorResult
    {
        $this->removeRelations($entity);
        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        return new RedirectProcessorResult(
            success: true,
            messageKey: 'member.flash.success.delete',
            targetUrl: $targetUrl,
            flashType: 'success'
        );
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
        $this->surveyResponseRepository->deleteResponsesByUser($member);
    }
}
