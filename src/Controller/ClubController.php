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
}
