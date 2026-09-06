<?php

declare(strict_types=1);

namespace App\State\Cluster\Provider;

use App\Dto\View\Cluster\ClusterView;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Mapper\Cluster\ClusterReadMapper;
use App\Repository\LicenceAgreementRepository;
use App\Repository\SessionRepository;
use App\Service\SeasonService;
use App\State\Cluster\Trait\ClusterDataProviderTrait;
use App\State\Interface\ComponentProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ClusterReadProvider implements ComponentProviderInterface
{
    use ClusterDataProviderTrait;

    public function __construct(
        private ClusterReadMapper $clusterReadMapper,
        protected readonly LicenceAgreementRepository $licenceAgreementRepository,
        protected readonly SessionRepository $sessionRepository,
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
}