<?php

declare(strict_types=1);

namespace App\State\Session\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\DialogType;
use App\Dto\Payload\SessionWarningToggle;
use App\Dto\View\Cluster\ClusterView;
use App\Dto\View\DialogModalView;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Mapper\Cluster\ClusterReadMapper;
use App\Mapper\DialogueModalMapper;
use App\Repository\LicenceAgreementRepository;
use App\Repository\SessionRepository;
use App\Service\Cluster\AbsentParticipantsService;
use App\Service\MessageService;
use App\Service\Placeholder\PlaceholderResolver;
use App\Service\SeasonService;
use App\Service\UrlContextService;
use App\State\Cluster\Trait\ClusterDataProviderTrait;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements FormComponentProviderInterface<SessionWarningToggle>
 */
class SessionWarningProvider implements FormComponentProviderInterface, TurboStreamProviderInterface
{
    use ClusterDataProviderTrait;

    public function __construct(
        protected readonly LicenceAgreementRepository $licenceAgreementRepository,
        protected readonly SessionRepository $sessionRepository,
        private MessageService $messageService,
        private PlaceholderResolver $placeholderResolver,
        private SeasonService $seasonService,
        private UrlContextService $urlContextService,
        private ClusterReadMapper $clusterReadMapper,
        private DialogueModalMapper $dialogModalMapper,
        private Security $security,
        private AbsentParticipantsService $absentParticipants,
    ) {
    }

    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        $member = $data->session->getMember();
        $rawMessage = $this->messageService->getMessageById($data->messageId);
        $message = $this->placeholderResolver->resolve($rawMessage, $member);

        return $this->dialogModalMapper->mapToView(
            DialogType::WARNING,
            'Rappel inscription',
            'Valider sa présence',
            $message,
            'lucide:square-check-big',
        );
    }

    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->modal#close',
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
            (!$data->isComplete()) ? ($this->absentParticipants)($data) : [],
            $targetUrl,
            $flashMessage,
        );
    }
}
