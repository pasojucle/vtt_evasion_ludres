<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\ViewModel\UserPresenter;
use App\Service\PaginatorService;
use App\Repository\UserRepository;
use App\ViewModel\OrdersPresenter;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderHeaderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

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
        if (null === $user) {
            $this->redirectToRoute('login');
        }

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
            /**@var PasswordAuthenticatedUserInterface|User $user */
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
}
