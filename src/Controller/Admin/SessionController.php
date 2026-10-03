<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDialogHandler;
use App\Core\Handler\ActionDirectHandler;
use App\Core\Handler\ActionFormHandler;
use App\Dto\Payload\AssociateResourcePayload;
use App\Dto\Payload\SessionCreateAdminPayload;
use App\Dto\Payload\SessionSwitch;
use App\Dto\Payload\SessionWarningToggle;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Form\Admin\SessionType;
use App\Form\SessionSwitchType;
use App\State\Cluster\Provider\ClusterUpdateProvider;
use App\State\Session\Processor\SessionCreateProcessor;
use App\State\Session\Processor\SessionDeleteProcessor;
use App\State\Session\Processor\SessionSwitchProcessor;
use App\State\Session\Processor\SessionToggleDirectProcessor;
use App\State\Session\Processor\SessionToggleProcessor;
use App\State\Session\Provider\SessionCreateProvider;
use App\State\Session\Provider\SessionDeleteProvider;
use App\State\Session\Provider\SessionSwitchProvider;
use App\State\Session\Provider\SessionWarningProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SessionController extends AbstractController
{
    #[Route('/admin/session/toggle/present/{session}', name: 'admin_session_toggle_present', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminPresent(
        Request $request,
        ClusterUpdateProvider $provider,
        SessionToggleDirectProcessor $processor,
        session $session,
        ActionDirectHandler $handler,
    ): Response {
        return $handler->handle($request, $session, $provider, $processor);
    }

    #[Route('/admin/session/message/{session}/{messageId}', name: 'admin_session_message', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminMessage(
        Request $request,
        SessionWarningProvider $provider,
        SessionToggleProcessor $processor,
        string $messageId,
        Session $session,
        ActionDialogHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new SessionWarningToggle($session, $messageId),
            $provider,
            $processor
        );
    }

    #[Route('/admin/groupe/change/{cluster}/{session}', name: 'admin_bike_ride_switch_cluster', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminClusterSwitch(
        Request $request,
        SessionSwitchProvider $provider,
        SessionSwitchProcessor $processor,
        Cluster $cluster,
        Session $session,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new SessionSwitch($session, $cluster),
            $provider,
            $processor,
            SessionSwitchType::class,
        );
    }

    #[Route('/admin/rando/inscription/{cluster}/{isFramer}', name: 'admin_session_add', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_EDIT', 'cluster')]
    public function adminSessionAdd(
        Request $request,
        SessionCreateProvider $provider,
        SessionCreateProcessor $processor,
        Cluster $cluster,
        bool $isFramer,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new SessionCreateAdminPayload($cluster, $isFramer),
            $provider,
            $processor,
            SessionType::class,
        );
    }

    #[Route('/admin/rando/supprime/{session}', name: 'admin_session_delete', methods: ['GET', 'POST'])]
    #[IsGranted('BIKE_RIDE_VIEW', 'session')]
    public function adminSessionDelete(
        Request $request,
        SessionDeleteProvider $provider,
        SessionDeleteProcessor $processor,
        session $session,
        ActionFormHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            new AssociateResourcePayload($session, $session->getCluster()),
            $provider,
            $processor
        );
    }
}
