<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\Filter\NotificationFilter;
use App\Entity\Notification;
use App\Form\Admin\NotificationType;
use App\State\Notification\Processor\NotificationToggleProcessor;
use App\State\Notification\Provider\NotificationAdminListProvider;
use App\State\Notification\Provider\NotificationToggleProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/popup', name: 'admin_notification_')]
class NotificationController extends AbstractCrudController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[IsGranted('MODAL_WINDOW_LIST')]
    public function list(
        Request $request,
        NotificationAdminListProvider $provider,
    ): Response {
        return $this->handleListAction(
            'admin_notification_list',
            NotificationFilter::class,
            $provider,
            $request
        );
    }

    #[Route('/', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('MODAL_WINDOW_ADD')]
    public function add(Request $request): Response
    {
        $notification = null;
        $form = $this->createForm(NotificationType::class, null);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $notification = $form->getData();
            $this->entityManager->persist($notification);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_notification_list');
        }

        return $this->render('notification/admin/edit.html.twig', [
            'notification' => $notification,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{notification}', name: 'edit', methods: ['GET', 'POST'], requirements:['notification' => '\d+'])]
    #[IsGranted('MODAL_WINDOW_EDIT', 'notification')]
    public function edit(Request $request, ?Notification $notification): Response
    {
        $form = $this->createForm(NotificationType::class, $notification);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_notification_list');
        }

        return $this->render('notification/admin/edit.html.twig', [
            'notification' => $notification,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/toggle/{notification}', name: 'toggle_disable', methods: ['GET', 'POST'])]
    #[IsGranted('MODAL_WINDOW_EDIT', 'notification')]
    public function toggle(
        Request $request,
        NotificationToggleProvider $provider,
        NotificationToggleProcessor $processor,
        Notification $notification
    ): Response {
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $processor->process($notification);

                return $this->redirectToRoute('admin_notification_list');
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('components/_dialog.modal.html.twig', [
            'dialog' => $provider->mapToView($notification),
            'form' => $form->createView(),
        ], $response);
    }
}
