<?php

namespace App\Controller;


use App\Entity\RegistrationStep;
use App\Form\RegistrationStepType;
use App\Service\SubscriptionService;
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
     * @Route("/club/inscription/{type}/{step}", name="register", defaults={"step":1})
     */
    public function register(
        Request $request,
        SubscriptionService $subscriptionService,
        string $type,
        ?int $step
    ): Response
    {
        return $this->render('club/registration.html.twig', [
            'type' => $type,
            'step' => $step,
            'progress' => $subscriptionService->getProgress($type, $step),
        ]);
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
            'registrationStep' => $this->registrationStepRepository->findOneByStep($step),
            'form' => $form->createView(),
        ]);
    }
}
