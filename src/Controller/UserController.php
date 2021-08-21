<?php

namespace App\Controller;

use App\Entity\Level;
use App\Form\IdentityType;
use App\Form\Admin\UserType;
use App\Form\UserFilterType;
use App\Service\UserService;
use App\Service\LicenceService;
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
        $this->userService = $userService;
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

        $this->session->set('user_return', $this->generateUrl('admin_users', ['filtered' => true, 'p' => $request->query->get('p')]));

        return $this->render('user/admin/users.html.twig', [
            'users' => $this->userService->convertPaginatorToUsers($users),
            'lastPage' => $paginator->lastPage($users),
            'form' => $form->createView(),
            'current_filters' => ['filtered' => (int) $filtered],
        ]);
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
     * @Route("/admin/identité/edit/{user}", name="admin_identity_edit")
     */
    public function adminIdentityEdit(
        Request $request,
        LicenceService $licenceService,
        UserService $userService,
        UserEntity $user
    ): Response
    {
        $currentSeason = $licenceService->getCurrentSeason();
        $seasonLicence = $user->getSeasonLicence($currentSeason);
        $identity = $user->getFirstIdentity();
        $form = $this->createForm(IdentityType::class, $identity, [
            'category' => $seasonLicence->getCategory(),
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $identity = $form->getData();
            if ($request->files->get('identity')) {
                $pictureFile = $request->files->get('identity')['pictureFile'];
                $newFilename = $userService->uploadFile($pictureFile);
                if (null !== $newFilename) {
                    $identity->setPicture($newFilename);
                }
            }
            $this->entityManager->flush();
            return $this->redirectToRoute('admin_user', ['user' => $user->getId()]);
        }
        return $this->render('identity/edit.html.twig', [
            'user' => new User($user),
            'form' => $form->createView(),
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
