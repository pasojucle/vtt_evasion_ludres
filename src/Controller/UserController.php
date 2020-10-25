<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\RegisterType;
use App\Service\EmailService;
use App\Form\EmailAddressType;
use App\Repository\UserRepository;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    private $entityManager;
    private $emailService;

    public function __construct(EntityManagerInterface $entityManager, EmailService $emailService)
    {
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
    }

    /**
     * @Route("/admin/user/edit/{user}", name="user_edit", defaults={"user":null}, requirements={"user"="\d*"}))
     */
    public function edit(Request $request, ?User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            if (null === $user->getId()) {
                $user->addRole('ROLE_USER');
            }

            if ($user->getSendActiveLink() || null === $user->getId()) {
                $this->emailService->register($user);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/users", name="user_list")
     */
    public function list(UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/user/register/{uuid}", name="user_register")
     */
    public function register(
        UserRepository $userRepository,
        LoginFormAuthenticator $authenticator, 
        GuardAuthenticatorHandler $guardHandler,
        UserPasswordEncoderInterface $passwordEncoder,
        Request $request,
        string $uuid
    ): Response
    {
        $user = $userRepository->findOneByUuid($uuid);

        if (null !== $user) {
            $form = $this->createForm(RegisterType::class, $user);

            $form->handleRequest($request);
            if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
                $user = $form->getData();
                $user->setIsActive(true);
                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user,          // the User object you just created
                    $request,
                    $authenticator, // authenticator whose onAuthenticationSuccess you want to use
                    'main'          // the name of your firewall in security.yaml
                );
            }

            return $this->render('user/register.html.twig', [
                'form' => $form->createView(),
            ]);

        }

        throw $this->createNotFoundException('Désolé, nous n\'avons pas trouvé la page demandée…');
    }

    /**
     * @Route("/user/reset/password", name="user_reset_password")
     */
    public function resetPassword(
        UserRepository $userRepository,
        Request $request
    ): Response
    {
        $form = $this->createForm(EmailAddressType::class);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $userRepository->findOneByEmail($data['email']);

            if (null !== $user) {
                $user->setUuid(uniqid());
                $this->entityManager->persist($user);
                $this->entityManager->flush();
    
                $this->emailService->register($user, $this->emailService::ACTION_RESET);
                $this->addFlash('success', "Un lien pour géner un nouveau mot de passe vient d'être envoyé");
            } else {
                $this->addFlash('danger', "l'adresse mail ".$data['email']." est inconnues.");
            }
        }

        return $this->render('user/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/delete/{user}", name="user_delete", requirements={"user"="\d+"},)
     */
    public function delete(
        Request $request,
        User $user
    ): Response
    {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl('user_delete', 
            [
                'user'=> $user->getId(),
            ]
        ),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/delete.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
