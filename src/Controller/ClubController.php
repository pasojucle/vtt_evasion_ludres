<?php

namespace App\Controller;


use App\Entity\RegistrationStep;
use App\Form\RegistrationStepType;
use App\Service\RegistrationService;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\RegistrationStepRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClubController extends AbstractController
{
    private RegistrationStepRepository $registrationStepRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(RegistrationStepRepository $registrationStepRepository, EntityManagerInterface $entityManager)
    {
        $this->registrationStepRepository = $registrationStepRepository;
        $this->entityManager = $entityManager;
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
        $progress = $registrationService->getProgress($type, $step);
        $form = $progress['form'];

        dump($form);

        return $this->render('club/registrationForm.html.twig', [
            'type' => $type,
            'step' => $step,
            'steps' => $progress['steps'],
            'form' => (null!== $form) ? $form->createView() : null,
            'prev' => $progress['prev'],
            'current' => $progress['current'],
            'next' => $progress['next'],
        ]);
    }
    /**
     * @Route("/club/register_form_validate/{type}/{step}", name="registration_form_validate", methods={"POST"})
     */
    public function registerFormValidate(
        Request $request,
        registrationService $registrationService,
        string $type,
        ?int $step
    ): Response
    {
        $progress = $registrationService->getProgress($type, $step);
        $form = $progress['form'];

        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $user = $registrationService->updateUser($form);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('user_registration_form', ['type' => $type, 'step' => $progress['next']]);
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
