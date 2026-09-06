<?php

declare(strict_types=1);

namespace App\State\Session\Provider;

use App\Dto\Payload\SessionCreateAdminPayload;
use App\Dto\State\TurboStreamContext;
use App\Dto\View\Cluster\ClusterView;
use App\Dto\View\Session\SessionAddSheetView;
use App\Entity\Enum\LevelType;
use App\Entity\Session;
use App\Mapper\Cluster\ClusterReadMapper;
use App\Repository\LicenceAgreementRepository;
use App\Repository\SessionRepository;
use App\Service\SeasonService;
use App\Service\SurveyService;
use App\Service\UrlContextService;
use App\State\Cluster\Trait\ClusterDataProviderTrait;
use App\State\Interface\FormAddComponentProviderInterface;
use App\State\Interface\TurboStreamProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements TurboStreamProviderInterface<SessionCreateAdminPayload>
 */
class SessionCreateProvider implements TurboStreamProviderInterface, FormAddComponentProviderInterface
{
    use ClusterDataProviderTrait;

    public function __construct(
        protected readonly LicenceAgreementRepository $licenceAgreementRepository,
        protected readonly SessionRepository $sessionRepository,
        private SeasonService $seasonService,
        private SurveyService $surveyService,
        private UrlContextService $urlContextService,
        private ClusterReadMapper $clusterReadMapper,
        private Security $security,
    ) {
    }

    public function getFormView(object $entity, ?string $fallback = null): SessionAddSheetView
    {
        return new SessionAddSheetView(
            title: 'Ajouter un participant',
            description: sprintf('Ajouter un nouveau participant au groupe %s', $entity->cluster->getTitle()),
            action: 'Ajouter',
        );
        
    }

    public function getFormOptions(object $entity): array
    {
        return [
            'attr' => [
                'data-controller' => 'form-modifier',
                'data-action'=> 'turbo:submit-end->sheet#handleFormSubmit',
            ]
        ];
    }

    public function setDefaultValues(object $entity): object
    {
        $bikeRide = $entity->cluster->getBikeRide();

        $currentSeason = $this->seasonService->getCurrentSeason();
        $minSeasonToTakePart = $this->seasonService->getMinSeasonToTakePart();
        $entity->season = ($minSeasonToTakePart < $currentSeason) ? null : $currentSeason;

        $entity->level = ($entity->isFramer) ? LevelType::FRAME->value : $entity->cluster->getLevel()?->getId();
        $entity->surveyResponses=  ($bikeRide->getSurvey())
                ? ['surveyResponses' => $this->surveyService->getSurveyResponsesFromBikeRide($bikeRide)]
                : null;

        return $entity;
    }

    public function getStreamView(object $entity, ?TurboStreamContext $context = null): ClusterView
    {
        $cluster = $entity->cluster;
        $userIds = $cluster->getSessions()->map(fn(Session $session) => $session->getUser()->getId())->toArray();
        $referer = $this->urlContextService->generateTargetUrl($context->route, $context->routeParam);


        return $this->clusterReadMapper->mapToView(
            $cluster,
            $this->security->isGranted('BIKE_RIDE_EDIT', $cluster->getBikeRide()),
            $this->authorizationsByUser($userIds),
            $this->participationsByUser($userIds),
            $this->seasonService->getCurrentSeason(),
            $referer,
        );
    }
}
