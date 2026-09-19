<?php

declare(strict_types=1);

namespace App\State\Cluster\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\View\Cluster\ClusterView;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Mapper\Cluster\ClusterReadMapper;
use App\Repository\LicenceAgreementRepository;
use App\Repository\SessionRepository;
use App\Service\SeasonService;
use App\Service\UrlContextService;
use App\State\Cluster\Trait\ClusterDataProviderTrait;
use Symfony\Bundle\SecurityBundle\Security;

class ClusterUpdateProvider implements TurboStreamProviderInterface
{
    use ClusterDataProviderTrait;

    public function __construct(
        private ClusterReadMapper $clusterReadMapper,
        protected readonly LicenceAgreementRepository $licenceAgreementRepository,
        protected readonly SessionRepository $sessionRepository,
        private SeasonService $seasonService,
        private UrlContextService $urlContextService,
        private Security $security,
    ) {
    }

    /**
     * @param Cluster $data
     */
    public function getStreamView(object $data, ?HandlerContext $context = null): ClusterView
    {
        $userIds = $data->getSessions()->map(fn (Session $session) => $session->getUser()->getId())->toArray();
        $bikeRide = $data->getBikeRide();

        return $this->clusterReadMapper->mapToView(
            $data,
            $this->security->isGranted('BIKE_RIDE_EDIT', $bikeRide),
            $this->authorizationsByUser($userIds),
            $this->participationsByUser($userIds),
            $this->seasonService->getCurrentSeason(),
            $this->urlContextService->encodeUrl('admin_cluster_list_activity', ['bikeRide' => $bikeRide->getId()]),
        );
    }
}
