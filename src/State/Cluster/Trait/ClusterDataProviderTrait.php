<?php

declare(strict_types=1);

namespace App\State\Cluster\Trait;

use App\Entity\LicenceAgreement;
use App\Repository\LicenceAgreementRepository;
use App\Repository\SessionRepository;

/**
 * @property-read LicenceAgreementRepository $licenceAgreementRepository
 * @property-read SessionRepository $sessionRepository
 */
trait ClusterDataProviderTrait
{
    /**
     * @param array<int, int> $userIds
     * @return array<int, array<int, LicenceAgreement>>
     */
    private function authorizationsByUser(array $userIds): array
    {
        $authorizations = $this->licenceAgreementRepository->findAuthorizationsByUsersAndAggrementId($userIds);
        $authorizationsByUser = [];

        /** @var LicenceAgreement $authorization */
        foreach ($authorizations as $authorization) {
            $agreement = $authorization->getAgreement();
            $memberId = (int) $authorization->getLicence()->getMember()->getId();
            $agreementId = (int) $agreement->getId();

            $authorizationsByUser[$memberId][$agreementId] = $authorization;
        }

        return $authorizationsByUser;
    }

    /**
     * @param array<int, int> $userIds
     * @return array<int, int>
     */
    private function participationsByUser(array $userIds): array
    {
        return array_column(
            $this->sessionRepository->findParticipationByUsers($userIds),
            'count',
            'userId'
        );
    }
}