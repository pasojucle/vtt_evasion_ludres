<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Core\Handler\ActionDirectHandler;
use App\Core\Handler\AutocompleteHandler;
use App\Core\Handler\DetailHandler;
use App\Core\Handler\JsonHandler;
use App\Core\Handler\ListExportHandler;
use App\Core\Handler\ListPaginatedHandler;
use App\Dto\Filter\UserFilter;
use App\Entity\Member;
use App\Entity\User;
use App\Repository\MemberRepository;
use App\State\User\Processor\LicenceNumberSendProcessor;
use App\State\User\Provider\LicenceNumberSendProvider;
use App\State\User\Provider\UserAutocompleteProvider;
use App\State\User\Provider\UserListEmailProvider;
use App\State\User\Provider\UserListExportProvider;
use App\State\User\Provider\UserListProvider;
use App\State\User\Provider\UserReadProvider;
use App\UseCase\User\GetFramersFiltered;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'admin_')]
class UserController extends AbstractController
{
    #[Route('/adherents', name: 'user_list', methods: ['GET'])]
    #[IsGranted('USER_LIST')]
    public function adminUsers(
        Request $request,
        UserListProvider $provider,
        ListPaginatedHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $provider,
        );
    }

    #[Route('/export/adherents', name: 'members_export', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function adminUsersExport(
        Request $request,
        UserListExportProvider $provider,
        ListExportHandler $handler,
    ): StreamedResponse {
        return $handler->handle(
            $request,
            UserFilter::class,
            $provider,
        );
    }

    #[Route('/emails/adherents', name: 'members_email_to_clipboard', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function adminEmailUsers(
        Request $request,
        UserListEmailProvider $provider,
        JsonHandler $handler,
    ): JsonResponse {
        return $handler->handle(
            $request,
            $provider,
            UserFilter::class,
        );
    }

    #[Route('/adherent/{user}', name: 'user_show', requirements:['user' => '\d+'], methods: ['GET'])]
    #[IsGranted('USER_SHARE', 'user')]
    public function adminUser(
        User $user,
        Request $request,
        UserReadProvider $provider,
        DetailHandler $handler,
    ): Response {
        return $handler->handle($request, $provider, $user);
    }

    #[Route('/send/numberlicence/{member}', name: 'send_number_licence', methods: ['GET'])]
    #[IsGranted('USER_EDIT', 'member')]
    public function adminSendLicence(
        Request $request,
        LicenceNumberSendProvider $provider,
        LicenceNumberSendProcessor $processor,
        Member $member,
        ActionDirectHandler $handler,
    ): Response {
        return $handler->handle(
            $request,
            $member,
            $provider,
            $processor
        );
    }

    #[Route('/adherent/autocomplete', name: 'member_autocomplete', methods: ['GET'])]
    #[IsGranted('USER_SHARE')]
    public function memberAutocomplete(
        Request $request,
        UserAutocompleteProvider $provider,
        AutocompleteHandler $handler,
    ): JsonResponse {
        return $handler->handle(
            $request,
            $provider,
            UserFilter::class,
        );
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
}
