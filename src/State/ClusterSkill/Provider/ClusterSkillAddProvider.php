<?php

declare(strict_types=1);

namespace App\State\ClusterSkill\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\Payload\ClusterSkillAddPayload;
use App\Dto\View\AssociateResourceSkillSheetView;
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

/**
 * @implements FormComponentProviderInterface<ClusterSkillAddPayload>
 */
class ClusterSkillAddProvider implements FormComponentProviderInterface, TurboStreamProviderInterface
{
    use ClusterDataProviderTrait;

    public function __construct(
        protected readonly LicenceAgreementRepository $licenceAgreementRepository,
        protected readonly SessionRepository $sessionRepository,
        private SeasonService $seasonService,
        private UrlContextService $urlContextService,
        private ClusterReadMapper $clusterReadMapper,
        private Security $security,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): AssociateResourceSkillSheetView
    {
        return new AssociateResourceSkillSheetView(
            title: 'Ajouter une compétence',
            description: sprintf('Ajouter une compétence au groupe %s', $data->cluster->getTitle()),
            action: 'Ajouter',
        );
    }

    /**
     * @param ClusterSkillAddPayload $data
     */
    public function getFormOptions(object $data): array
    {
        return [
            'clusterId' => $data->cluster->getId(),
            'attr' => [
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
                'data-controller' => 'form-modifier',
                // 'data-turbo-action' => 'replace',
            ],
        ];
    }

    /** @param Cluster $data  */
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
