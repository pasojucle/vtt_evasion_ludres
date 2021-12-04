<?php

namespace App\Controller;

use App\Entity\Level;
use App\Form\Admin\UserType;
use App\Form\UserFilterType;
use App\Service\UserService;
use App\Service\ExportService;
use App\Service\MailerService;
use App\Service\LicenceService;
use App\Service\PaginatorService;
use App\Repository\UserRepository;
use App\ViewModel\OrdersPresenter;
use App\Entity\User as  UserEntity;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderHeaderRepository;
use App\Form\Admin\RegistrationFilterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserService $userService,
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager
    )
    {
        $this->session = $this->requestStack->getSession();
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
            $filtered = true;
            $request->query->set('p', 1);
        }
        
        $this->session->set('admin_users_filters', $filters);
        $query = $this->userRepository->findMemberQuery($filters);
        $users = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        $this->session->set('user_return', $this->generateUrl('admin_users', ['filtered' => true, 'p' => $request->query->get('p')]));

        return $this->render('user/admin/users.html.twig', [
            'users' => $this->userService->convertPaginatorToUsers($users),
            'lastPage' => $paginator->lastPage($users),
            'form' => $form->createView(),
            'current_filters' => ['filtered' => (int) $filtered],
            'count' => $paginator->total($users),
        ]);
    }

    /**
     * @Route("/admin/export/adherents", name="admin_users_export")
     */
    public function adminUsersExport(
        ExportService $exportService
    ): Response
    {
        $filters = $this->session->get('admin_users_filters');

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
    /**
     * @Route("/admin/adherent/{user}", name="admin_user")
     */
    public function adminUser(
        UserEntity $user
    ): Response
    {

        return $this->render('user/admin/user.html.twig', [
            'user' => $this->userService->convertToUser($user),
            'referer' => $this->session->get('user_return'),
        ]);
    }

    /**
     * @Route("/admin/adherent/edit/{user}", name="admin_user_edit")
     */
    public function adminUserEdit(
        Request $request,
        LicenceService $licenceService,
        UserEntity $user
    ): Response
    {
        $licence = $user->getLastLicence();
        $form = $this->createForm(UserType::class, $user, [
            'category' => $licence->getCategory(),
            'season_licence' => $licence,
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            if (null !== $user->getLevel() && $user->getLevel()->getType() === Level::TYPE_FRAME) {
                $user->addRole('ROLE_FRAME');
            } else {
                $user->removeRole('ROLE_FRAME');
            }
            
            $this->entityManager->flush();
            return $this->redirectToRoute('admin_user', ['user' => $user->getId()]);
        }
        return $this->render('user/admin/edit.html.twig', [
            'user' => $this->userService->convertToUser($user),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/inscriptions/{filtered}", name="admin_registrations", defaults={"filtered"=0})
     */
    public function adminRegistration(
        PaginatorService $paginator,
        Request $request,
        bool $filtered
    ): Response
    {
        $filters = ($filtered) ? $this->session->get('admin_registrations_filters'): null;

        $form = $this->createForm(RegistrationFilterType::class, $filters);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
            $this->session->set('admin_registrations_filters', $filters);
            $filtered = true;
            $request->query->set('p', 1);
        }

        $query =  $this->userRepository->findUserLicenceInProgressQuery($filters);
        $users =  $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);
        $this->session->set('user_return', $this->generateUrl('admin_registrations', ['filtered' => true, 'p' => $request->query->get('p')]));

        return $this->render('user/admin/registrations.html.twig', [
            'users' => $this->userService->convertPaginatorToUsers($users),
            'lastPage' => $paginator->lastPage($users),
            'current_filters' => ['filtered' => (int) $filtered],
            'count' => $paginator->total($users),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/send/numberlicence/{user}", name="admin_send_number_licence")
     */
    public function adminSendLicence(
        MailerService $mailerService,
        UserEntity $user
    ): Response
    {
        $identity = $user->getFirstIdentity();
        $mailerService->sendMailToMember([
            'subject' => 'Votre numero de licence',
            'email' => $identity->getEmail(),
            'name' => $identity->getName(),
            'firstName' => $identity->getFirstName(),
            'licenceNumber' => $user->getLicenceNumber(),
        ], 'EMAIL_LICENCE_VALIDATE');

        $this->addFlash('success', 'Le messsage à été envoyé avec succès');

        return $this->redirectToRoute('admin_user_edit', ['user' => $user->getId()]);
    }

    /**
     * @Route("/mon-compte", name="user_account")
     */
    public function userAccount(
        OrderHeaderRepository $ordersHeaderRepository,
        PaginatorService $paginator,
        OrdersPresenter $ordersPresenter,
        Request $request
    ): Response
    {
        $user = $this->getUser();

        $query = $ordersHeaderRepository->findOrdersByUserQuery($user);
        $ordersHeader = $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        $ordersPresenter->present($ordersHeader);
        return $this->render('user/account.html.twig', [
            'user' => $this->userService->convertToUser($user),
            'orders' => $ordersPresenter->viewModel()->orders,
        ]);
    }

    /**
     *
     * @Route("/mot_de_passe/modifier", name="change_password")
     */
    public function changePassword(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
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
                ->setPasswordMustBeChanged(false);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('succes', 'Votre mot de passe a bien été modifé.');

            return $this->redirectToRoute('user_account');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     "/utilisateur/list/select2",
     *     name="user_list_select2"
     * )
     */
    public function userListSelect2(
        UserRepository $userRepository,
        Request $request
    ): JsonResponse {
        $query = $request->query->get('q');
        $hasCurrentSeason = $request->query->get('has_current_season');

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
