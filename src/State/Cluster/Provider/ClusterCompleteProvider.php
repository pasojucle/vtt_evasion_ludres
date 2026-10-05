<?php

declare(strict_types=1);

namespace App\State\Cluster\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\DialogType;
use App\Dto\View\Cluster\ClusterView;
use App\Dto\View\DialogModalView;
use App\Entity\Cluster;
use App\Entity\Member;
use App\Entity\Session;
use App\Mapper\Cluster\ClusterReadMapper;
use App\Mapper\DialogueModalMapper;
use App\Repository\LicenceAgreementRepository;
use App\Repository\SessionRepository;
use App\Service\Cluster\AbsentParticipantsService;
use App\Service\SeasonService;
use App\Service\UrlContextService;
use App\State\Cluster\Trait\ClusterDataProviderTrait;
use Symfony\Bundle\SecurityBundle\Security;

class ClusterCompleteProvider implements FormComponentProviderInterface, TurboStreamProviderInterface
{
    use ClusterDataProviderTrait;

    public function __construct(
        private ClusterReadMapper $clusterReadMapper,
        private DialogueModalMapper $dialogModalMapper,
        protected readonly LicenceAgreementRepository $licenceAgreementRepository,
        protected readonly SessionRepository $sessionRepository,
        private SeasonService $seasonService,
        private UrlContextService $urlContextService,
        private Security $security,
        private AbsentParticipantsService $absentParticipants,
    ) {
    }

    /**
     * @param Cluster $data
     */
    public function getView(object $data, ?HandlerContext $context = null): DialogModalView
    {
        $absentParticipants = ($this->absentParticipants)($data);
        $absentParticipantFullnames = array_map(fn(Member $member) => $member->getIdentity()->getFullName(), $absentParticipants);

        $message =  sprintf('<p>%s %s</p><p>%s<p>',
            implode(', ', $absentParticipantFullnames),
            (1 < count($absentParticipants)) ? 'sont absents.' : 'est absent.',
            'Êtes-vous sûre de vouloir valider le groupe ?'
        );

        return $this->dialogModalMapper->mapToView(
            DialogType::WARNING,
            'Groupe imcomplet',
            'Valider le groupe',
            $message,
            'lucide:square-check-big',
        );
    }

    /**
     * @param Cluster $data
     */
    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'attr' => [
                'data-action' => 'turbo:submit-end->modal#close',
                'data-dispatch-event' => sprintf('cluster:export#cluster#%s', $data->getId()),
            ],
        ];
    }

    /**
     * @param Cluster $data
     */
    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ClusterView
    {
        $userIds = $data->getSessions()->map(fn (Session $session) => $session->getUser()->getId())->toArray();
        $bikeRide = $data->getBikeRide();

        return $this->clusterReadMapper->mapToView(
            $data,
            $this->security->isGranted('BIKE_RIDE_EDIT', $bikeRide),
            $this->authorizationsByUser($userIds),
            $this->participationsByUser($userIds),
            $this->seasonService->getCurrentSeason(),
            (!$data->isComplete()) ? ($this->absentParticipants)($data) : [],
            $this->urlContextService->encodeUrl('admin_cluster_list_activity', ['bikeRide' => $bikeRide->getId()]),
            $flashMessage,
        );
    }
}
