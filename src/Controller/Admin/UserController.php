<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Parameter;
use App\Entity\User;
use App\Form\Admin\UserBoardRoleType;
use App\Form\Admin\UserType;
use App\Repository\UserRepository;
use App\Service\MailerService;
use App\Service\MessageService;
use App\UseCase\User\GetFramersFiltered;
use App\UseCase\User\GetMembersFiltered;
use App\UseCase\User\GetOverviewSeason;
use App\UseCase\User\GetParticipation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserDtoTransformer $userDtoTransformer,
        private readonly GetOverviewSeason $getOverviewSeason,
    ) {
    }

    #[Route('/adherents/{filtered}', name: 'users', methods: ['GET', 'POST'], defaults:['filtered' => 0])]
    #[IsGranted('USER_LIST')]
    public function adminUsers(
        GetMembersFiltered $getMembersFiltered,
        MessageService $messageService,
        Request $request,
        bool $filtered
    ): Response {
        $params = $getMembersFiltered->list($request, $filtered);

        $params['settings'] = [
            'parameters' => $this->entityManager->getRepository(Parameter::class)->findByParameterGroupName('USER'),
            'routes' => [
                ['name' => 'admin_levels', 'label' => 'Niveaux'],
                ['name' => 'admin_skill_list', 'label' => 'Compétences'],
                ['name' => 'admin_board_role_list', 'label' => 'Roles du bureau et comité'],
            ],
            'messages' => $messageService->getMessagesBySectionName('USER'),
        ];
        if ($request->isMethod('POST')) {
            return $this->render('user/admin/_member_list.html.twig', $params);
        }
        
        return $this->render('user/admin/users.html.twig', $params);
    }

    #[Route('/export/adherents', name: 'members_export', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function adminUsersExport(
        GetMembersFiltered $getMembersFiltered,
        Request $request
    ): Response {
        return $getMembersFiltered->export($request);
    }

    #[Route('/emails/adherents', name: 'members_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
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
    #[IsGranted('USER_SHARE', 'user')]
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

    #[Route('/adherent/participation/{user}/{filtered}', name: 'user_participation', methods: ['GET', 'POST'], requirements: ['user' => '\d+'], defaults:['filtered' => false])]
    #[IsGranted('USER_VIEW', 'user')]
    public function adminUserParticipation(
        GetParticipation $getParticipation,
        Request $request,
        User $user,
        bool $filtered
    ): Response {
        return $this->render('user/admin/participation.html.twig', $getParticipation->execute($request, $user, $filtered));
    }

    #[Route('/adherent/participation/export/{user}', name: 'user_participation_export', methods: ['GET', 'POST'], requirements: ['user' => '\d+'])]
    #[IsGranted('USER_VIEW', 'user')]
    public function adminUserParticipationExeport(
        GetParticipation $getParticipation,
        Request $request,
        User $user,
    ): Response {
        return $getParticipation->export($request, $user);
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
        MessageService $messageService,
        User $user
    ): RedirectResponse {
        $userDto = $this->userDtoTransformer->identifiersFromEntity($user);
        $subject = 'Votre numero de licence';
        $mailerService->sendMailToMember($userDto, $subject, $messageService->getMessageByName('EMAIL_LICENCE_VALIDATE'));

        $this->addFlash('success', 'Le messsage à été envoyé avec succès');

        return $this->redirectToRoute('admin_user_edit', [
            'user' => $user->getId(),
        ]);
    }

    #[Route('/adherent/autocomplete', name: 'member_autocomplete', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function memberAutocomplete(
        GetMembersFiltered $getMembersFiltered,
        Request $request
    ): JsonResponse {
        return new JsonResponse(['results' => $getMembersFiltered->choices($request->query->all())]);
    }

    #[Route('/encadrant/autocomplete', name: 'framer_autocomplete', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function framerAutocomplete(
        GetFramersFiltered $getFramersFiltered,
        Request $request
    ): JsonResponse {
        $filters['fullName'] = $request->query->get('q');
        $filters['bikeRideId'] = (int) $request->query->get('bikeRideId');
        $filters['availability'] = $request->query->get('availability');
    
        return new JsonResponse(['results' => $getFramersFiltered->choices($filters)]);
    }

    
    #[Route('/all/user/autocomplete', name: 'all_user_autocomplete', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function allUserAutocomplete(
        Request $request,
        UserRepository $userRepository,
    ): JsonResponse {
        $query = $request->query->get('query');
        $users = (null !== $query)
            ? $userRepository->findByNumberLicenceOrFullName($query)
            : $userRepository->findAllAsc();
        $results = [];
        foreach ($users as $user) {
            $text = $user->getLicenceNumber();
            if (null !== $user->GetFirstIdentity()) {
                $text .= ' ' . $user->__toString();
            }
            $results[] = [
                'value' => $user->getId(),
                'text' => $text,
            ];
        }

        return new JsonResponse(['results' => $results]);
    }

    #[Route('/synthese/saison/{filtered}/{tab}', name: 'overview_season', defaults:['filtered' => false, 'tab' => GetOverviewSeason::TAB_NEW_REGISTRATIONS], methods: ['GET', 'POST'])]
    #[IsGranted('USER_SHARE')]
    public function overviewSeasonMembers(
        Request $request,
        bool $filtered,
        int $tab,
    ): Response {
        return $this->render('user/admin/overviewSeason.html.twig', $this->getOverviewSeason->getMembers($request, $filtered, $tab));
    }

    #[Route('/export/synthese/saison', name: 'overview_season_export', methods: ['GET', 'POST'])]
    #[IsGranted('USER_SHARE')]
    public function exportOverviewSeason(
        Request $request,
    ): Response {
        return $this->getOverviewSeason->export($request);
    }

    #[Route('/emails/synthese/saison', name: 'overview_season_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function overviewSeasonToClicpboard(
        Request $request
    ): JsonResponse {
        return new JsonResponse($this->getOverviewSeason->emailsToClipboard($request));
    }
}
