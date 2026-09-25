<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDirectHandler;
use App\Core\Handler\ActionFormHandler;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\Payload\AssociateResourcePayload;
use App\Dto\Payload\SessionCreateAdminPayload;
use App\Dto\Payload\SessionSwitch;
use App\Dto\State\ViewContext;
use App\Entity\Cluster;
use App\Entity\Session;
use App\Form\Admin\SessionType;
use App\Form\SessionSwitchType;
use App\Service\MessageService;
use App\Service\ReplaceKeywordsService;
use App\State\Cluster\Provider\ClusterUpdateProvider;
use App\State\Session\Processor\SessionCreateProcessor;
use App\State\Session\Processor\SessionDeleteProcessor;
use App\State\Session\Processor\SessionSwitchProcessor;
use App\State\Session\Processor\SessionToggleProcessor;
use App\State\Session\Provider\SessionCreateProvider;
use App\State\Session\Provider\SessionDeleteProvider;
use App\State\Session\Provider\SessionSwitchProvider;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SessionController extends AbstractCrudController
{
    #[Route('/admin/session/toggle/present/{session}', name: 'admin_session_toggle_present', methods: ['GET'])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminPresent(
        Request $request,
        ClusterUpdateProvider $provider,
        SessionToggleProcessor $processor,
        session $session,
        ActionDirectHandler $handler,
    ): Response {
        return $handler->handle($request, $session, $provider, $processor);
    }

    #[Route('/admin/session/message/{session}', name: 'admin_session_message', methods: ['GET'], options:['expose' => true])]
    #[IsGranted('BIKE_RIDE_LIST')]
    public function adminMessage(
        Session $session,
        UserDtoTransformer $userDtoTransformer,
        MessageService $messageService,
        ReplaceKeywordsService $replaceKeywordsService,
    ): Response {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl('admin_session_present'),
        ]);
        $form->add('sessionId', HiddenType::class, ['data' => $session->getId()]);


        $userDto = $userDtoTransformer->getHeaderFromEntity($session->getMember());
        $member = $session->getMember();

        $message = '';
        if ($userDto->mustProvideRegistration) {
            $message = $messageService->getMessageById('BIKE_RIDE_MUST_PROVIDE_REGISTRATION');
        }
        if ($userDto->isEndTesting) {
            $message = $messageService->getMessageById('BIKE_RIDE_END_TESTING');
        }

        $message = $replaceKeywordsService->replaceUserData($message, $member);

        return $this->render('session/admin/message.html.twig', [
            'session' => $session,
            'message' => $message,
            'form' => $form->createView(),
        ]);
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
    ): Response {
        return $this->handleFormComponentAction(
            $request,
            new SessionCreateAdminPayload($cluster, $isFramer),
            $provider,
            $processor,
            SessionType::class,
            new ViewContext(
                route: $request->attributes->get('_route'),
                routeParams: $request->attributes->get('_route_params'),
                page: $request->query->getInt('page', 1),
                fallback: $request->query->get('_redirect_to'),
            ),
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
