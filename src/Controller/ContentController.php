<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Link;
use App\Form\ContactType;
use App\Repository\BikeRideRepository;
use App\Repository\ContentRepository;
use App\Repository\LevelRepository;
use App\Repository\LinkRepository;
use App\Service\MailerService;
use App\Service\OrderByService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private OrderByService $orderByService,
        private ContentRepository $contentRepository
    ) {
    }

    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(
        LinkRepository $linkRepository,
        ContentRepository $contentRepository,
        BikeRideRepository $bikeRideRepository
    ): Response {
        $homeContents = $contentRepository->findHomeContents();
        $linksBikeRide = $linkRepository->findByPosition(Link::POSITION_HOME_BIKE_RIDE);
        $linksFooter = $linkRepository->findByPosition(Link::POSITION_HOME_FOOTER);
        $bikeRides = $bikeRideRepository->findEnableView();

        return $this->render('content/home.html.twig', [
            'links_bike_ride' => $linksBikeRide,
            'links_footer' => $linksFooter,
            'bikeRides' => $bikeRides,
            'home_contents' => $homeContents,
        ]);
    }

    #[Route('/club', name: 'club', methods: ['GET'])]
    public function club(): Response
    {
        return $this->render('content/club.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('club'),
        ]);
    }

    #[Route('/ecole_vtt/disciplines', name: 'school_practices', methods: ['GET'])]
    public function schoolPractices(
        LevelRepository $levelRepository
    ): Response {
        return $this->render('content/school.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('school_practices'),
            'levels' => $levelRepository->findAllTypeMemberNotProtected(),
            'background_color' => 'red',
            'background_img' => 'ecole_vtt_disciplines.jpg',
        ]);
    }

    #[Route('/ecole_vtt/presentation', name: 'school_overview', methods: ['GET'])]
    public function schoolOverview(
        LevelRepository $levelRepository
    ): Response {
        return $this->render('content/school.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('school_overview'),
            'background_color' => 'green',
            'background_img' => 'ecole_vtt_groupe.jpg',
        ]);
    }

    #[Route('/ecole_vtt/fonctionnement', name: 'school_operating', methods: ['GET'])]
    public function schoolOperating(
        LevelRepository $levelRepository
    ): Response {
        return $this->render('content/school.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('school_operating'),
            'background_color' => 'blue',
            'background_img' => 'ecole_vtt_fonctionnement.jpg',
        ]);
    }

    #[Route('/ecole_vtt/equipement', name: 'school_equipment', methods: ['GET'])]
    public function schoolEquipment(
        LevelRepository $levelRepository
    ): Response {
        return $this->render('content/school.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('school_equipment'),
            'background_color' => 'green',
            'background_img' => 'ecole_vtt_equipement.jpg',
        ]);
    }

    #[Route('/contact', name: 'contact', methods: ['GET', 'POST'])]
    public function contact(
        Request $request,
        MailerService $mailerService
    ): Response {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data['subject'] = 'Message envoyé depuis le site vttevasionludres.fr';
            if ($mailerService->sendMailToClub($data) && $mailerService->sendMailToMember($data, 'EMAIL_FORM_CONTACT')) {
                $this->addFlash('success', 'Votre message a bien été envoyé');

                return $this->redirectToRoute('contact');
            }
            $this->addFlash('danger', 'Une erreure est survenue');
        }

        return $this->render('content/contact.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('contact'),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reglement', name: 'rules', methods: ['GET'])]
    public function rules(): Response
    {
        return $this->render('content/rules.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('rules'),
        ]);
    }

    #[Route('/mentions/legales', name: 'legal_notices', methods: ['GET'])]
    public function legalNotices(): Response
    {
        return $this->render('content/legal_notices.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('legal_notices'),
        ]);
    }

    #[Route('/aide/connexion', name: 'login_help', methods: ['GET'])]
    public function loginHelp(): Response
    {
        return $this->render('content/login_help.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('login_help'),
            'background_color' => 'red',
            'background_img' => 'ecole_vtt_disciplines.jpg',
        ]);
    }
}
