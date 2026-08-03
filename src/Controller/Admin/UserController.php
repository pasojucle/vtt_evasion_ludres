<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Dto\Filter\UserFilter;
use App\Entity\Member;
use App\Entity\User;
use App\Form\Admin\LicenceMemberType;
use App\Repository\MemberRepository;
use App\State\User\Processor\LicenceNumberSendProcessor;
use App\State\User\Provider\UserListProvider;
use App\State\User\Provider\UserReadProvider;
use App\UseCase\User\GetFramersFiltered;
use App\UseCase\User\GetOverviewSeason;
use App\UseCase\User\GetParticipation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
class UserController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserDtoTransformer $userDtoTransformer,
        private readonly GetOverviewSeason $getOverviewSeason,
    ) {
    }

    #[Route('/adherents', name: 'user_list', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminUsers(
        UserListProvider $provider,
        Request $request,
    ): Response {
        return $this->handleListAction(
            UserFilter::class,
            $provider,
            $request
        );
    }

    #[Route('/export/adherents', name: 'members_export', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function adminUsersExport(
        UserListProvider $provider,
        Request $request
    ): StreamedResponse {
        return $this->handleExportAction(
            $request,
            UserFilter::class,
            $provider,
            'export_adherents.csv'
        );
    }

    #[Route('/emails/adherents', name: 'members_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function adminEmailUsers(
        UserListProvider $provider,
        Request $request
    ): JsonResponse {
        /**  @var UserFilter $filter */
        $filter = $provider->getHydratedDto($request->query->all(), UserFilter::class);

        return new JsonResponse($provider->copyEmailListToClipboard($filter));
    }

    #[Route('/adherent/{user}', name: 'user_show', requirements:['user' => '\d+'], methods: ['GET'])]
    #[Route('/inscription/adherent/{user}', name: 'registration_user', requirements:['user' => '\d+'], methods: ['GET'])]
    #[Route('/adherent/calendrier/{user}', name: 'bike_rides_user', requirements:['user' => '\d+'], methods: ['GET'])]
    #[Route('/adherent/assurance/{user}', name: 'coverage_user', requirements:['user' => '\d+'], methods: ['GET'])]
    #[IsGranted('USER_SHARE', 'user')]
    public function adminUser(
        User $user,
        Request $request,
        UserReadProvider $provider,
    ): Response {
        return $this->handleComponentAction(
            $request,
            $provider,
            $user
        );
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
    public function adminUserParticipationExport(
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
        $form = $this->createForm(LicenceMemberType::class, $user, [
            'category' => $licence->getCategory(),
            'season_licence' => $licence,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $this->entityManager->flush();

            return $this->redirectToRoute('admin_user_show', [
                'user' => $user->getId(),
            ]);
        }

        return $this->render('user/admin/edit.html.twig', [
            'user' => $this->userDtoTransformer->fromEntity($user),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/send/numberlicence/{member}', name: 'send_number_licence', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function adminSendLicence(
        LicenceNumberSendProcessor $processor,
        Member $member
    ): Response {

    $result = $processor->process($member);

    return $this->render($result->laziTemplate, [
        'flashes' => [$result->flashMessage->type => [$result->flashMessage->message]],
        ], new Response('', Response::HTTP_OK, [
            'Content-Type' => 'text/vnd.turbo-stream.html',
        ]));
    }

    #[Route('/adherent/autocomplete', name: 'member_autocomplete', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function memberAutocomplete(
        UserListProvider $provider,
        Request $request
    ): JsonResponse {
        /**  @var UserFilter $filter */
        $filter = $provider->getHydratedDto($request->query->all(), UserFilter::class);

        return new JsonResponse(['results' => $provider->getAutocompleteChoices($filter)]);
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
        MemberRepository $memberRepository,
    ): JsonResponse {
        $query = $request->query->get('query');
        $users = (null !== $query)
            ? $memberRepository->findByNumberLicenceOrFullName($query)
            : $memberRepository->findAllAsc();
        $results = [];
        foreach ($users as $user) {
            $text = $user->getLicenceNumber();
            if (null !== $user->getIdentity()) {
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
