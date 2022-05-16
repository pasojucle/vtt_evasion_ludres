<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Level;
use App\Entity\User;
use App\Form\Admin\UserType;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\PaginatorService;
use App\UseCase\User\GetMembersFiltered;
use App\UseCase\User\GetParticipation;
use App\ViewModel\SessionsPresenter;
use App\ViewModel\UserPresenter;
use App\ViewModel\UsersPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UsersPresenter $usersPresenter,
        private UserPresenter $userPresenter,
        private PaginatorService $paginator
    ) {
    }

    #[Route('/adherents/{filtered}', name: 'users', methods: ['GET', 'POST'], defaults:['filtered' => 0])]
    public function adminUsers(
        GetMembersFiltered $getMembersFiltered,
        Request $request,
        bool $filtered
    ): Response {
        return $this->render(
            'user/admin/users.html.twig',
            $getMembersFiltered->list($request, $filtered)
        );
    }

    #[Route('/export/adherents', name: 'members_export', methods: ['GET'])]
    public function adminUsersExport(
        GetMembersFiltered $getMembersFiltered,
        Request $request
    ): Response {
        return $getMembersFiltered->export($request);
    }

    #[Route('/emails/adherents', name: 'members_email_to_clipboard', methods: ['GET'])]
    public function adminEmailUsers(
        GetMembersFiltered $getMembersFiltered,
        Request $request
    ): JsonResponse {
        return new JsonResponse($getMembersFiltered->emailsToClipboard($request));
    }

    #[Route('/adherent/{user}', name: 'user', methods: ['GET'])]
    public function adminUser(
        User $user,
        Request $request
    ): Response {
        $session = $request->getSession();
        $this->userPresenter->present($user);

        return $this->render('user/admin/user.html.twig', [
            'user' => $this->userPresenter->viewModel(),
            'referer' => $session->get('admin_user_redirect'),
        ]);
    }

    #[Route('/admin/adherent/participation/{user}/{filtered}', name: 'user_participation', methods: ['GET', 'POST'], defaults:['filtered' => false])]
    public function adminUserParticipation(
        GetParticipation $getParticipation,
        Request $request,
        User $user,
        bool $filtered
    ): Response {
        return $this->render(
            'user/admin/participation.html.twig',
            $getParticipation->execute($request, $user, $filtered)
        );
    }

    #[Route('/admin/adherent/edit/{user}', name: 'user_edit', methods: ['GET', 'POST'])]
    public function adminUserEdit(
        Request $request,
        User $user
    ): Response {
        $licence = $user->getLastLicence();
        $form = $this->createForm(UserType::class, $user, [
            'category' => $licence->getCategory(),
            'season_licence' => $licence,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            if (null !== $user->getLevel() && Level::TYPE_FRAME === $user->getLevel()->getType()) {
                $user->addRole('ROLE_FRAME');
            } else {
                $user->removeRole('ROLE_FRAME');
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('admin_user', [
                'user' => $user->getId(),
            ]);
        }
        $this->userPresenter->present($user);

        return $this->render('user/admin/edit.html.twig', [
            'user' => $this->userPresenter->viewModel(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/send/numberlicence/{user}', name: 'send_number_licence', methods: ['GET'])]
    public function adminSendLicence(
        MailerService $mailerService,
        User $user
    ): Response {
        $identity = $user->getFirstIdentity();
        $mailerService->sendMailToMember([
            'subject' => 'Votre numero de licence',
            'email' => $identity->getEmail(),
            'name' => $identity->getName(),
            'firstName' => $identity->getFirstName(),
            'licenceNumber' => $user->getLicenceNumber(),
        ], 'EMAIL_LICENCE_VALIDATE');

        $this->addFlash('success', 'Le messsage à été envoyé avec succès');

        return $this->redirectToRoute('admin_user_edit', [
            'user' => $user->getId(),
        ]);
    }

    #[Route('/adhérent/choices', name: 'member_choices', methods: ['GET'])]
    public function memberChoices(
        GetMembersFiltered $getMembersFiltered,
        Request $request
    ): JsonResponse {
        $query = $request->query->get('q');

        $filters = json_decode($request->query->get('filters'), true);

        return new JsonResponse($getMembersFiltered->choices($filters, $query));
    }
}
