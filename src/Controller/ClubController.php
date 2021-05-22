<?php

namespace App\Controller;


use App\Entity\RegistrationStep;
use App\Form\RegistrationStepType;
use App\Service\RegistrationService;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\RegistrationStepRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ClubController extends AbstractController
{
    private RegistrationStepRepository $registrationStepRepository;
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;

    public function __construct(RegistrationStepRepository $registrationStepRepository, EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $this->registrationStepRepository = $registrationStepRepository;
        $this->entityManager = $entityManager;
        $this->session = $session;
    }
    
    /**
     * @Route("/club", name="club")
     */
    public function index(): Response
    {
        return $this->render('club/index.html.twig', [
            'controller_name' => 'ClubController',
        ]);
    }

    /**
     * @Route("/club/inscription", name="registration")
     */
    public function register(): Response
    {
        return $this->render('club/registration.html.twig');
    }

    /**
     * @Route("/club/bulletin_inscription/{type}/{step}", name="registration_form", defaults={"step":null})
     * @Route("/mon_compte/bulletin_inscription/{type}/{step}", name="user_registration_form", defaults={"step":null})
     */
    public function registerForm(
        Request $request,
        registrationService $registrationService,
        string $type,
        ?int $step
    ): Response
    {
        if (!$step) {
            $this->session->remove('healthQuestions');
        }
        $progress = $registrationService->getProgress($type, $step);
        $form = $progress['form'];

        return $this->render('club/registrationForm.html.twig', [
            'type' => $type,
            'step' => $step,
            'steps' => $progress['steps'],
            'form' => (null!== $form) ? $form->createView() : null,
            'prev' => $progress['prev'],
            'current' => $progress['current'],
            'next' => $progress['next'],
            'user' => $progress['user'],
        ]);
    }
    /**
     * @Route("/club/register_form_validate/{type}/{step}", name="registration_form_validate", methods={"POST"})
     */
    public function registerFormValidate(
        Request $request,
        registrationService $registrationService,
        UserPasswordEncoderInterface $passwordEncoder,
        LoginFormAuthenticator $authenticator,
        GuardAuthenticatorHandler $guardHandler,
        string $type,
        ?int $step
    ): Response
    {
        $progress = $registrationService->getProgress($type, $step);
        $form = $progress['form'];
        $route = (4 > $step) ? 'registration_form': 'user_registration_form';

        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            dump($form);
            $manualAuthenticating = false;
            if ($form->get('plainPassword') && $form->get('plainPassword')->getData()) {
                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $manualAuthenticating = true;
            }

            if (null === $user->getIdentities()->first()->getName()) {
                $healthQuestions = $user->getHealth()->getHealthQuestions();
                $this->session->set('healthQuestions', $healthQuestions);
            } else {
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
            $this->session->remove('registrationPath');
            if ($manualAuthenticating) {
                $this->session->set('registrationPath', $route);
                // after validating the user and saving them to the database
                // authenticate the user and use onAuthenticationSuccess on the authenticator
                $guardHandler->authenticateUserAndHandleSuccess(
                    $user,          // the User object you just created
                    $request,
                    $authenticator, // authenticator whose onAuthenticationSuccess you want to use
                    'main'          // the name of your firewall in security.yaml
                );
            }
        }

        return $this->redirectToRoute($route, ['type' => $type, 'step' => $progress['next']]);
    }

    /**
     * @Route("/admin/inscription/", name="admin_registration_steps")
     */
    public function adminRegistrationSteps(
        Request $request
    ): Response
    {
        return $this->render('club/admin/registrationTypes.html.twig', [
            'registrationSteps' => $this->registrationStepRepository->findAll(),
        ]);
    }

    /**
     * @Route("/admin/inscription/{step}", name="admin_registration_step")
     */
    public function adminRegistrationStep(
        Request $request,
        RegistrationStep $step
    ): Response
    {
        $form = $this->createForm(RegistrationStepType::class, $step);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $step = $form->getData();
            $this->entityManager->persist($step);
            $this->entityManager->flush();
        }

        return $this->render('club/admin/registrationStep.html.twig', [
            'registrationStep' => $step,
            'form' => $form->createView(),
        ]);
    }
}
