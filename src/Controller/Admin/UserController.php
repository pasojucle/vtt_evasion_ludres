<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Level;
use App\Entity\User as  UserEntity;
use App\Form\Admin\RegistrationFilterType;
use App\Form\Admin\UserType;
use App\Repository\UserRepository;
use App\Service\ExportService;
use App\Service\MailerService;
use App\Service\PaginatorService;
use App\UseCase\User\GetMembersFiltered;
use App\UseCase\Registration\GetRegistrationsFiltered;
use App\ViewModel\UserPresenter;
use App\ViewModel\UsersPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
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
        private UserPresenter $userPresenter
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
            $getMembersFiltered->execute($request, $filtered)
        );
    }

    #[Route('/export/adherents', name: 'users_export', methods: ['GET'])]
    public function adminUsersExport(
        ExportService $exportService,
        Request $request
    ): Response {
        $session = $request->getSession();
        $filters = $session->get('admin_users_filters');

        $query = $this->userRepository->findMemberQuery($filters);
        $users = $query->getQuery()->getResult();
        $content = $exportService->exportUsers($users);

        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_email.csv'
        );

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('/adherent/{user}', name: 'user', methods: ['GET'])]
    public function adminUser(
        UserEntity $user,
        Request $request
    ): Response {
        $session = $request->getSession();
        $this->userPresenter->present($user);

        return $this->render('user/admin/user.html.twig', [
            'user' => $this->userPresenter->viewModel(),
            'referer' => $session->get('admin_user_redirect'),
        ]);
    }

    #[Route('/admin/adherent/edit/{user}', name: 'user_edit', methods: ['GET', 'POST'])]
    public function adminUserEdit(
        Request $request,
        UserEntity $user
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

    #[Route('/inscriptions/{filtered}', name: 'registrations', methods: ['GET', 'POST'], defaults:['filtered' => 0])]
    public function adminRegistration(
        GetRegistrationsFiltered $getRegistrationsFiltered,
        Request $request,
        bool $filtered
    ): Response {

        return $this->render(
            'user/admin/registrations.html.twig',
            $getRegistrationsFiltered->execute($request, $filtered)
        );
    }

    #[Route('/send/numberlicence/{user}', name: 'send_number_licence', methods: ['GET'])]
    public function adminSendLicence(
        MailerService $mailerService,
        UserEntity $user
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
}
