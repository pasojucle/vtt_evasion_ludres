<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\EmailMessageType;
use App\Repository\ContentRepository;
use App\Service\IdentityService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserDtoTransformer $userDtoTransformer
    ) {
    }

    #[Route('/mon-compte', name: 'user_account', methods: ['GET'])]
    public function userAccount(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var ?User $user */
        $user = $this->getUser();
        if (null === $user) {
            $this->redirectToRoute('login');
        }

        return $this->render('user/account.html.twig', [
            'user' => $this->userDtoTransformer->fromEntity($user),
        ]);
    }

    #[Route('/mot_de_passe/modifier', name: 'change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        if (null === $user) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PasswordAuthenticatedUserInterface&user $user */
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


    #[Route('mon_compte/demande/modification', name: 'user_change_infos', methods: ['GET', 'POST'])]
    public function changeInfos(
        Request $request,
        MailerService $mailerService,
        ContentRepository $contentRepository
    ): Response {

        /** @var ?User $user */
        $user = $this->getUser();
        $form = $this->createForm(EmailMessageType::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userDto = $this->userDtoTransformer->fromEntity($user);
            $data['user'] = $userDto;
            $data['name'] = $userDto->member->name;
            $data['firstName'] = $userDto->member->firstName;
            $data['email'] = $userDto->mainEmail;
            $data['subject'] = 'Demande de modification d\'informations personnelles';
            if ($mailerService->sendMailToClub($data) && $mailerService->sendMailToMember($data, 'EMAIL_CHANGE_USER_INFOS')) {
                $this->addFlash('success', 'Votre message a bien été envoyé');

                return $this->redirectToRoute('user_change_infos');
            }
            $this->addFlash('danger', 'Une erreure est survenue');
        }

        return $this->render('user/change_infos.html.twig', [
            'content' => $contentRepository->findOneByRoute('user_change_infos'),
            'form' => $form->createView(),
        ]);
    }
}
