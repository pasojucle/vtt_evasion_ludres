<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\User;
use App\Form\Admin\UserBoardRoleType;
use App\Form\Admin\UserType;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\UseCase\User\GetFramersFiltered;
use App\UseCase\User\GetMembersFiltered;
use App\UseCase\User\GetParticipation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserDtoTransformer $userDtoTransformer
    ) {
    }

    #[Route('/adherents/{filtered}', name: 'users', methods: ['GET', 'POST'], defaults:['filtered' => 0])]
    #[IsGranted('USER_LIST')]
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
    #[IsGranted('USER_LIST')]
    public function adminUsersExport(
        GetMembersFiltered $getMembersFiltered,
        Request $request
    ): Response {
        return $getMembersFiltered->export($request);
    }

    #[Route('/emails/adherents', name: 'members_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminEmailUsers(
        GetMembersFiltered $getMembersFiltered,
        Request $request
    ): JsonResponse {
        return new JsonResponse($getMembersFiltered->emailsToClipboard($request));
    }

    #[Route('/adherent/{user}', name: 'user', requirements:['user' => '\d+'], methods: ['GET'])]
    #[Route('/inscription/adherent/{user}', name: 'registration_user', requirements:['user' => '\d+'], methods: ['GET'])]
    #[Route('/adherent/calendrier/{user}', name: 'bike_rides_user', requirements:['user' => '\d+'], methods: ['GET'])]
    #[Route('/adherent/assurance/{user}', name: 'coverage_user', requirements:['user' => '\d+'], methods: ['GET'])]
    #[IsGranted('USER_VIEW', 'user')]
    public function adminUser(
        User $user,
        Request $request
    ): Response {
        $session = $request->getSession();

        return $this->render('user/admin/user.html.twig', [
            'user' => $this->userDtoTransformer->fromEntity($user),
            'referer' => $session->get('admin_user_redirect'),
        ]);
    }

    #[Route('/adherent/participation/{user}/{filtered}', name: 'user_participation', methods: ['GET', 'POST'], defaults:['filtered' => false])]
    #[IsGranted('USER_VIEW', 'user')]
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

    #[Route('/adherent/edit/{user}', name: 'user_edit', requirements:['user' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'user')]
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

            $this->entityManager->flush();

            return $this->redirectToRoute('admin_user', [
                'user' => $user->getId(),
            ]);
        }

        return $this->render('user/admin/edit.html.twig', [
            'user' => $this->userDtoTransformer->fromEntity($user),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/adherent/role/{user}', name: 'user_board_role', requirements:['user' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('USER_EDIT', 'user')]
    public function adminUserRole(
        Request $request,
        User $user
    ): Response {
        $form = $this->createForm(UserBoardRoleType::class, $user);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $this->entityManager->flush();

            return $this->redirectToRoute('admin_user', [
                'user' => $user->getId(),
            ]);
        }

        return $this->render('user/admin/boardRole.html.twig', [
            'user' => $this->userDtoTransformer->fromEntity($user),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/send/numberlicence/{user}', name: 'send_number_licence', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'user')]
    public function adminSendLicence(
        MailerService $mailerService,
        User $user
    ): RedirectResponse {
        $userDto = $this->userDtoTransformer->fromEntity($user);

        $mailerService->sendMailToMember([
            'subject' => 'Votre numero de licence',
            'email' => $userDto->mainEmail,
            'name' => $userDto->member->name,
            'firstName' => $userDto->member->firstName,
            'licenceNumber' => $userDto->licenceNumber,
        ], 'EMAIL_LICENCE_VALIDATE');

        $this->addFlash('success', 'Le messsage à été envoyé avec succès');

        return $this->redirectToRoute('admin_user_edit', [
            'user' => $user->getId(),
        ]);
    }

    #[Route('/adherent/choices', name: 'member_choices', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function memberChoices(
        GetMembersFiltered $getMembersFiltered,
        Request $request
    ): JsonResponse {
        $query = $request->query->get('q');

        $filtersQuery = $request->query->get('filters');
        $filters = ($filtersQuery) ? json_decode($filtersQuery, true) : [];

        return new JsonResponse($getMembersFiltered->choices($filters, $query));
    }

    #[Route('/encadrant/choices', name: 'framer_choices', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function framerChoices(
        GetFramersFiltered $getFramersFiltered,
        Request $request
    ): JsonResponse {
        $query = $request->query->get('q');
        
        $filtersQuery = $request->query->get('filters');
        $filters = ($filtersQuery) ? json_decode($filtersQuery, true) : [];

        return new JsonResponse($getFramersFiltered->choices($filters, $query));
    }

    
    #[Route('/all/user/choices', name: 'all_user_choices', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function allUserChoices(
        Request $request,
        UserRepository $userRepository,
    ): JsonResponse {
        $query = $request->query->get('q');
        $users = (null !== $query)
            ? $userRepository->findByNumberLicenceOrFullName($query)
            : $userRepository->findAllAsc();
        $response = [];
        foreach ($users as $user) {
            $text = $user->getLicenceNumber();
            if (null !== $user->GetFirstIdentity()) {
                $text .= ' ' . $user->GetFirstIdentity()->getName() . ' ' . $user->GetFirstIdentity()->getFirstName();
            }
            $response[] = [
                'id' => $user->getId(),
                'text' => $text,
            ];
        }

        return new JsonResponse($response);
    }
}
