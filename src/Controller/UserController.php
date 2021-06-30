<?php

namespace App\Controller;

use App\Form\UserFilterType;
use App\Service\UserService;
use App\Service\PaginatorService;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * @Route("/admin/adherents", name="admin_users")
     */
    public function adminUsers(
        PaginatorService $paginator,
        UserRepository $userRepository,
        UserService $userService,
        Request $request
    ): Response
    {
        $filters = [];
        $form = $this->createForm(UserFilterType::class, $filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // $filters = $eventService->getFiltersByData($data);
            // $form = $this->createForm(EventFilterType::class, $filters);
        }

        $query =  $userRepository->findMemberQuery($filters);
        $users =  $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('user/admin/users.html.twig', [
            'users' => $userService->convertUsers($users),
            'lastPage' => $paginator->lastPage($users),
            'form' => $form->createView(),
        ]);
    }
}
