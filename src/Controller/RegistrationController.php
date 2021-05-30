<?php

namespace App\Controller;

use App\Entity\Licence;
use App\Entity\RegistrationStep;
use App\Form\RegistrationStepType;
use App\Service\RegistrationService;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\RegistrationStepRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
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
     * @Route("/inscription/info", name="registration_detail")
     */
    public function registrationDetail(): Response
    {
        return $this->render('registration/detail.html.twig');
    }

    /**
     * @Route("/inscription", name="registration_member")
     */
    public function registration(): Response
    {
        $this->session->remove('registrationMaxStep');
        
        return $this->render('registration/member.html.twig');
    }


    /**
     * @Route("/inscription/{step}", name="registration_form")
     * @Route("/mon_compte/inscription/{step}", name="user_registration_form")
     */
    public function registerForm(
        Request $request,
        registrationService $registrationService,
        int $step
    ): Response
    {
        if ((int) $this->session->get('registrationMaxStep') < $step) {
            $this->session->set('registrationMaxStep', $step);
        }
        if (null !== $this->getUser() && $request->request->get('_route') === 'registration_form') {

        }

        if (!$step) {
            $this->session->remove('healthQuestions');
        }
        $progress = $registrationService->getProgress($step);
        $form = $progress['form'];

        return $this->render('registration/registrationForm.html.twig', [
            'step' => $step,
            'steps' => $progress['steps'],
            'form' => (null!== $form) ? $form->createView() : null,
            'prev' => $progress['prev'],
            'current' => $progress['current'],
            'next' => $progress['next'],
            'user' => $progress['user'],
            'season_licence' => $progress['seasonLicence'],
            'maxStep' => $this->session->get('registrationMaxStep'),
        ]);
    }
    /**
     * @Route("/register_form_validate/{step}", name="registration_form_validate", methods={"POST"})
     */
    public function registerFormValidate(
        Request $request,
        registrationService $registrationService,
        UserPasswordEncoderInterface $passwordEncoder,
        LoginFormAuthenticator $authenticator,
        GuardAuthenticatorHandler $guardHandler,
        ?int $step
    ): Response
    {
        $progress = $registrationService->getProgress($step);
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

            if (null !== $user->getIdentities()->first()->getBirthDate()) {
                $today = new DateTime();
                $age = $today->diff($user->getIdentities()->first()->getBirthDate());
                $category =  (18 > (int) $age->format('%y')) ? Licence::CATEGORY_MINOR : Licence::CATEGORY_ADULT;
                dump($category);
                $season = $registrationService->getSeason();
                $user->getSeasonLicence($season)->setCategory($category);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

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

        return $this->redirectToRoute($route, ['step' => $progress['next']]);
    }

    /**
     * @Route("/admin/inscription/", name="admin_registration_steps")
     */
    public function adminRegistrationSteps(
        Request $request
    ): Response
    {
        return $this->render('registration/admin/registrationTypes.html.twig', [
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

        return $this->render('registration/admin/registrationStep.html.twig', [
            'registrationStep' => $step,
            'form' => $form->createView(),
        ]);
    }
}
