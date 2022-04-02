<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Repository\OrderHeaderRepository;
use App\Repository\UserRepository;
use App\Service\PaginatorService;
use App\ViewModel\OrdersPresenter;
use App\ViewModel\UserPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPresenter $userPresenter
    ) {
    }

    #[Route('/mon-compte', name: 'user_account', methods: ['GET'])]
    public function userAccount(
        OrderHeaderRepository $ordersHeaderRepository,
        PaginatorService $paginator,
        OrdersPresenter $ordersPresenter,
        Request $request
    ): Response {
        $user = $this->getUser();

        $query = $ordersHeaderRepository->findOrdersByUserQuery($user);
        $ordersHeader = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        $ordersPresenter->present($ordersHeader);
        $this->userPresenter->present($user);

        return $this->render('user/account.html.twig', [
            'user' => $this->userPresenter->viewModel(),
            'orders' => $ordersPresenter->viewModel()->orders,
        ]);
    }

    #[Route('/mot_de_passe/modifier', name: 'change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();

        if (null === $user) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword)
                ->setPasswordMustBeChanged(false)
            ;
            $this->entityManager->flush();

            $this->addFlash('succes', 'Votre mot de passe a bien été modifé.');

            return $this->redirectToRoute('user_account');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    #[Route('/utilisateur/list/select2', name: 'user_list_select2', methods: ['GET'])]
    public function userListSelect2(
        UserRepository $userRepository,
        Request $request
    ): JsonResponse {
        $query = $request->query->get('q');
        $hasCurrentSeason = (bool) $request->query->get('has_current_season');

        $users = $userRepository->findByFullName($query, $hasCurrentSeason);

        $response = [];

        foreach ($users as $user) {
            $response[] = [
                'id' => $user->getId(),
                'text' => $user->GetFirstIdentity()->getName().' '.$user->GetFirstIdentity()->getFirstName(),
            ];
        }

        return new JsonResponse($response);
    }
}
