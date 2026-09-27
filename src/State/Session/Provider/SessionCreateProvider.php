<?php

declare(strict_types=1);

namespace App\State\Session\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\InputInitializerInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Contract\Provider\TurboStreamUpdateProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\Payload\SessionCreateAdminPayload;
use App\Dto\View\Cluster\ClusterView;
use App\Dto\View\Session\SessionAddFormView;
use App\Dto\View\SheetFormUpdateView;
use App\Dto\View\SheetFormWrapperView;
use App\Entity\Enum\LevelType;
use App\Entity\Session;
use App\Mapper\Cluster\ClusterReadMapper;
use App\Repository\LicenceAgreementRepository;
use App\Repository\SessionRepository;
use App\Service\SeasonService;
use App\Service\SurveyService;
use App\State\Cluster\Trait\ClusterDataProviderTrait;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements TurboStreamProviderInterface<SessionCreateAdminPayload>
 */
class SessionCreateProvider implements FormComponentProviderInterface, TurboStreamProviderInterface, InputInitializerInterface, TurboStreamUpdateProviderInterface
{
    use ClusterDataProviderTrait;

    private const string FRAME_ID = 'cluster-participant-add';

    public function __construct(
        protected readonly LicenceAgreementRepository $licenceAgreementRepository,
        protected readonly SessionRepository $sessionRepository,
        private SeasonService $seasonService,
        private SurveyService $surveyService,
        private ClusterReadMapper $clusterReadMapper,
        private Security $security,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): SheetFormWrapperView
    {
        return new SheetFormWrapperView(
            title: 'Ajouter un participant',
            description: sprintf('Ajouter un nouveau participant au groupe %s', $data->cluster->getTitle()),
            action: 'Ajouter',
            frameId: self::FRAME_ID,
            formView: new SessionAddFormView(),
        );
    }

    public function getFormOptions(object $data): array
    {
        return [
            'attr' => [
                'data-controller' => 'form-modifier',
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit',
            ]
        ];
    }

    public function setDefaultValues(object $data): object
    {
        $bikeRide = $data->cluster->getBikeRide();

        $currentSeason = $this->seasonService->getCurrentSeason();
        $minSeasonToTakePart = $this->seasonService->getMinSeasonToTakePart();
        $data->season = ($minSeasonToTakePart < $currentSeason) ? null : $currentSeason;

        $data->level = ($data->isFramer) ? LevelType::FRAME->value : $data->cluster->getLevel()?->getId();
        $data->surveyResponses = ($bikeRide->getSurvey())
                ? ['surveyResponses' => $this->surveyService->getSurveyResponsesFromBikeRide($bikeRide)]
                : null;

        return $data;
    }

    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ClusterView
    {
        $cluster = $data->cluster;
        $userIds = $cluster->getSessions()->map(fn (Session $session) => $session->getUser()->getId())->toArray();

        return $this->clusterReadMapper->mapToView(
            $cluster,
            $this->security->isGranted('BIKE_RIDE_EDIT', $cluster->getBikeRide()),
            $this->authorizationsByUser($userIds),
            $this->participationsByUser($userIds),
            $this->seasonService->getCurrentSeason(),
            $context->encodedFallback,
        );
    }

    
    public function getUpdateStreamView(object $data, ?HandlerContext $context = null): SheetFormUpdateView
    {
        return new SheetFormUpdateView(
            frameId: self::FRAME_ID,
            formView: new SessionAddFormView(),
        );
    }
}
