<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Licence;
use App\Entity\Member;
use App\Entity\OrderHeader;
use App\Repository\OrderLineRepository;
use App\Repository\SessionRepository;
use App\Repository\SurveyResponseRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SurveyResponseRepository $surveyResponseRepository,
        private OrderLineRepository $orderLineRepository,
        private readonly LicenceService $licenceService,
        private SessionRepository $sessionRepository,
        private SeasonService $seasonService,
    ) {
    }

    public function deleteUser(Member $member): void
    {
        $allData = [
            [
                'entity' => $member,
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
        $this->surveyResponseRepository->deleteResponsesByUser($member);

        $this->entityManager->remove($member);
        $this->entityManager->flush();
    }

    public function licenceIsActive(?Member $member): bool
    {
        if (!$member) {
            return false;
        }
        $lastLicence = $member->getLastLicence();
        return $this->licenceService->isActive($lastLicence);
    }

    public function isEndTesting(?Licence $lastLicence, int $sessionPresents): bool
    {
        if ($lastLicence && in_array($lastLicence->getState(), [LicenceStateEnum::TRIAL_FILE_SUBMITTED, LicenceStateEnum::TRIAL_FILE_RECEIVED, LicenceStateEnum::TRIAL_COMPLETED])) {
            return 2 < $sessionPresents;
        }

        return false;
    }

    public function trialSessionsPresent(?Licence $lastLicence, Member $member): int
    {
        if ($lastLicence && in_array($lastLicence->getState(), [LicenceStateEnum::TRIAL_FILE_SUBMITTED, LicenceStateEnum::TRIAL_FILE_RECEIVED, LicenceStateEnum::TRIAL_COMPLETED])) {
            return $this->sessionRepository->findParticipationByUser($member);
        }

        return 0;
    }

    public function mustProvideRegistration(?Licence $lastLicence, int $licencesTotal): bool
    {
        $currentSeason = $this->seasonService->getCurrentSeason();

        return 1 === $licencesTotal && $lastLicence?->getSeason() === $currentSeason && LicenceStateEnum::YEARLY_FILE_SUBMITTED === $lastLicence->getState();
    }
}
