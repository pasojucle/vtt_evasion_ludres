<?php

declare(strict_types=1);

namespace App\State\Cluster\Provider;

use App\Dto\View\Cluster\ClusterView;
use App\Entity\Cluster;
use App\Entity\LicenceAgreement;
use App\Entity\Session;
use App\Mapper\Cluster\ClusterReadMapper;
use App\Repository\LicenceAgreementRepository;
use App\Repository\SessionRepository;
use App\Service\SeasonService;
use App\State\Interface\ComponentProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ClusterReadProvider implements ComponentProviderInterface
{
    public function __construct(
        private ClusterReadMapper $clusterReadMapper,
        private LicenceAgreementRepository $licenceAgreementRepository,
        private SessionRepository $sessionRepository,
        private SeasonService $seasonService,
        private Security $security,
    ){}

    /**
     * @param Cluster $entity
     */
    public function getView(object $entity, ?string $fallback = null, ?string $referer = null): ClusterView
    {
        $userIds = $entity->getSessions()->map(fn(Session $session) => $session->getUser()->getId())->toArray();


        return $this->clusterReadMapper->mapToView(
            $entity,
            $this->security->isGranted('BIKE_RIDE_EDIT', $entity->getBikeRide()),
            $this->authorizationsByUser($userIds),
            $this->participationsByUser($userIds),
            $this->seasonService->getCurrentSeason(),
            $referer,
        );
    }

    /**
     *  
     * @param array<int, int> $userIds
     */
    private function authorizationsByUser(Array $userIds): array
    {

        $authorizations = $this->licenceAgreementRepository->findAuthorizationsByUsersAndAggrementId($userIds);
        $authorizationsByUser = [];
        /** @var LicenceAgreement $authorization*/
        foreach($authorizations as $authorization) {
            $agreement = $authorization->getAgreement();
            $authorizationsByUser[$authorization->getLicence()->getMember()->getId()][$agreement->getId()] = $authorization;
        }
        return $authorizationsByUser;
    }

    /**
     *  
     * @param array<int, int> $userIds
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