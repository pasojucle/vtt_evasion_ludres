<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Identity;
use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\EmailMessageType;
use App\Repository\ContentRepository;
use App\Repository\IdentityRepository;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\ParameterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserDtoTransformer $userDtoTransformer
    ) {
    }

    #[Route('/mon-compte/profil', name: 'user_account', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function userAccount(): Response
    {
        /** @var ?User $user */
        $user = $this->getUser();

        return $this->render('user/account.html.twig', [
            'user' => $this->userDtoTransformer->fromEntity($user),
        ]);
    }

    #[Route('/mon-compte/mot-de-passe', name: 'change_password', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();

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


    #[Route('/mon-compte/demande/modification', name: 'user_change_infos', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function changeInfos(
        Request $request,
        MailerService $mailerService,
        ContentRepository $contentRepository,
        MessageService $messageService,
    ): Response {
        /** @var ?User $user */
        $user = $this->getUser();
        $form = $this->createForm(EmailMessageType::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $userDto = $this->userDtoTransformer->fromEntity($user);
            $subject = 'Demande de modification d\'informations personnelles';
            $data['subject'] = $subject;
            $data['name'] = $userDto->member->name;
            $data['firstName'] = $userDto->member->firstName;
            $data['email'] = $userDto->mainEmail;

            if ($mailerService->sendMailToClub($data) && $mailerService->sendMailToMember($userDto, $subject, $messageService->getMessageByName('EMAIL_CHANGE_USER_INFOS'))) {
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


    #[Route('/unique/member/{name}/{firstName}', name: 'unique_member', methods: ['POST', 'GET'], options:['expose' => true])]
    public function uniqueMember(
        Request $request,
        IdentityRepository $identityRepository,
        AuthenticationUtils $authenticationUtils,
        string $name,
        string $firstName
    ): Response {
        $name = trim(urldecode($name));
        $firstName = trim(urldecode($firstName));
        /** @var Identity $identity */
        $identity = $identityRepository->findOneByNameAndFirstName($name, $firstName);
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl($request->attributes->get('_route'), $request->attributes->get('_route_params')),
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            if ($identity) {
                $request->getSession()->set($authenticationUtils->getLastUsername(), $identity->getUser()->getLicenceNumber());
            }
            return $this->redirectToRoute('user_account');
        }
        
        return $this->render('user/unique_member.modal.html.twig', [
            'name' => $name,
            'first_name' => $firstName,
            'form' => $form->createView(),
        ]);
    }
}
