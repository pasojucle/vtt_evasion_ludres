<?php

declare(strict_types=1);

namespace App\State\Session\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\Payload\SessionDelete;
use App\Dto\View\Cluster\ClusterView;
use App\Dto\View\DialogModalView;
use App\Entity\Session;
use App\Mapper\Cluster\ClusterReadMapper;
use App\Mapper\DestructiveModalMapper;
use App\Repository\LicenceAgreementRepository;
use App\Repository\SessionRepository;
use App\Service\SeasonService;
use App\Service\UrlContextService;
use App\State\Cluster\Trait\ClusterDataProviderTrait;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements FormComponentProviderInterface<SessionDelete>
 */
class SessionDeleteProvider implements FormComponentProviderInterface, TurboStreamProviderInterface
{
    use ClusterDataProviderTrait;

    public function __construct(
        protected readonly LicenceAgreementRepository $licenceAgreementRepository,
        protected readonly SessionRepository $sessionRepository,
        private SeasonService $seasonService,
        private UrlContextService $urlContextService,
        private ClusterReadMapper $clusterReadMapper,
        private DestructiveModalMapper $destructiveModalMapper,
        private Security $security,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        return $this->destructiveModalMapper->mapToView(
            sprintf(
                '<p>Etes vous certain de supprimer<br> %s',
                $data->session->getUser()->getIdentity()->getFullName(),
            )
        );
    }

    public function getFormOptions(object $data): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->modal#close',
            ],
        ];
    }

    /** @param object $data  */
    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ClusterView
    {
        $userIds = $data->getSessions()->map(fn (Session $session) => $session->getUser()->getId())->toArray();
        $targetUrl = $this->urlContextService->decodeUrl($context->encodedFallback);

        return $this->clusterReadMapper->mapToView(
            $data,
            $this->security->isGranted('BIKE_RIDE_EDIT', $data->getBikeRide()),
            $this->authorizationsByUser($userIds),
            $this->participationsByUser($userIds),
            $this->seasonService->getCurrentSeason(),
            $targetUrl,
            $flashMessage,
        );
    }
}
