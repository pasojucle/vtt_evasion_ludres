<?php

declare(strict_types=1);

namespace App\State\Cluster\Provider;

use App\Core\Dto\HandlerContext;
use App\Dto\View\Cluster\ClusterView;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Mapper\Cluster\ClusterFrameReadMapper;
use App\Repository\LicenceAgreementRepository;
use App\Repository\MemberRepository;
use App\Repository\SessionRepository;
use App\Service\SeasonService;
use App\Service\UrlContextService;
use App\State\Cluster\Trait\ClusterDataProviderTrait;
use App\Core\Contract\Provider\ComponentProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ClusterFrameReadProvider implements ComponentProviderInterface
{
    use ClusterDataProviderTrait;

    public function __construct(
        private ClusterFrameReadMapper $clusterFrameReadMapper,
        protected readonly LicenceAgreementRepository $licenceAgreementRepository,
        protected readonly SessionRepository $sessionRepository,
        private MemberRepository $memberRepository,
        private SeasonService $seasonService,
        private UrlContextService $urlContextService,
        private Security $security,
    ) {
    }

    /**
     * @param Cluster $data
     */
    public function getView(object $data, ?HandlerContext $context = null): ClusterView
    {
        $userIds = $data->getSessions()->map(fn (Session $session) => $session->getUser()->getId())->toArray();
        $bikeRide = $data->getBikeRide();

        return $this->clusterFrameReadMapper->mapToView(
            $data,
            $this->security->isGranted('BIKE_RIDE_EDIT', $bikeRide),
            $this->authorizationsByUser($userIds),
            $this->participationsByUser($userIds),
            $this->memberRepository->findFramersByBikeRide($bikeRide),
            $this->seasonService->getCurrentSeason(),
            $this->urlContextService->encodeUrl('admin_cluster_list_activity', ['bikeRide' => $bikeRide->getId()]),
        );
    }
}
