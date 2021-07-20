<?php

namespace App\Controller;

use App\Form\UserFilterType;
use App\Service\UserService;
use App\DataTransferObject\User;
use App\Service\PaginatorService;
use App\Repository\UserRepository;
use App\Entity\User as  UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;
    private UserService $userService;

    public function __construct(
        UserRepository $userRepository,
        UserService $userService,
        SessionInterface $session,
        EntityManagerInterface $entityManager
    )
    {
        $this->userRepository = $userRepository;
        $this->userService =$userService;
        $this->entityManager = $entityManager;
        $this->session = $session;
    }
    /**
     * @Route("/admin/adherents/{filtered}", name="admin_users", defaults={"filtered"=0})
     */
    public function adminUsers(
        PaginatorService $paginator,
        Request $request,
        bool $filtered
    ): Response
    {
        $filters = ($filtered) ? $this->session->get('admin_users_filters'): null;

        $form = $this->createForm(UserFilterType::class, $filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $this->session->set('admin_users_filters', $filters);
            $filtered = true;
            $request->query->set('p', 1);
        }

        $query =  $this->userRepository->findMemberQuery($filters);
        $users =  $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('user/admin/users.html.twig', [
            'users' => $this->userService->convertUsers($users),
            'lastPage' => $paginator->lastPage($users),
            'form' => $form->createView(),
            'current_filters' => ['filtered' => $filtered],
        ]);
    }

    /**
     * @Route("/admin/adherent/{user}", name="admin_user")
     */
    public function adminUser(
        Request $request,
        UserEntity $user
    ): Response
    {

        return $this->render('user/admin/user.html.twig', [
            'user' => new User($user),
            'referer' => $request->headers->get('referer'),
        ]);
    }

    /**
     * @Route("/mon-compte", name="user_account")
     */
    public function userAccount(
        Request $request
    ): Response
    {
        $user = $this->getUser();
        
        return $this->render('user/account.html.twig', [
            'user' => new User($user),
        ]);
    }
}
