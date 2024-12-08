<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\DtoTransformer\BikeRideDtoTransformer;
use App\Dto\DtoTransformer\ContentDtoTransformer;
use App\Dto\DtoTransformer\DocumentationDtoTransformer;
use App\Dto\DtoTransformer\SummaryDtoTransformer;
use App\Dto\DtoTransformer\UserDtoTransformer;
use App\Entity\Link;
use App\Entity\SlideshowImage;
use App\Entity\User;
use App\Form\ContactType;
use App\Repository\BikeRideRepository;
use App\Repository\ContentRepository;
use App\Repository\DocumentationRepository;
use App\Repository\LevelRepository;
use App\Repository\LinkRepository;
use App\Repository\LogRepository;
use App\Repository\SlideshowImageRepository;
use App\Repository\SummaryRepository;
use App\Service\IdentityService;
use App\Service\LogService;
use App\Service\MailerService;
use App\Service\MessageService;
use App\Service\ProjectDirService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ContentController extends AbstractController
{
    public function __construct(
        private readonly ContentRepository $contentRepository,
        private readonly LogService $logService,
        private readonly LogRepository $logRepository,
    ) {
    }

    #[Route('/', name: 'home', methods: ['GET'])]
    public function home(
        LinkRepository $linkRepository,
        ContentRepository $contentRepository,
        BikeRideRepository $bikeRideRepository,
        ContentDtoTransformer $contentDtoTransformer,
        BikeRideDtoTransformer $bikeRideDtoTransformer
    ): Response {
        $homeContents = $contentRepository->findHomeContents();
        $linksBikeRide = $linkRepository->findByPosition(Link::POSITION_HOME_BIKE_RIDE);
        $linksFooter = $linkRepository->findByPosition(Link::POSITION_HOME_FOOTER);
        $bikeRides = $bikeRideRepository->findEnableView();
        $homeContent = $homeContents[0]->getParent();

        return $this->render('content/home.html.twig', [
            'backgrounds' => $homeContent->getBackgrounds(),
            'links_bike_ride' => $linksBikeRide,
            'links_footer' => $linksFooter,
            'bikeRides' => $bikeRideDtoTransformer->fromEntities($bikeRides),
            'home_contents' => $contentDtoTransformer->fromEntities($homeContents)->homeContents,
        ]);
    }

    #[Route('/', name: 'home_post', methods: ['POST'])]
    #[Route('/reglement', name: 'rules_post', methods: ['POST'])]
    #[Route('/notifications', name: 'notification_list_post', methods: ['POST'])]
    public function noRoutePost(): Response
    {
        return new Response(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/club', name: 'club_overview', methods: ['GET'])]
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
        ]);
    }

    #[Route('/ecole_vtt/presentation', name: 'school_overview', methods: ['GET'])]
    public function schoolOverview(): Response
    {
        return $this->render('content/school.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('school_overview'),
            'background_color' => 'green',
        ]);
    }

    #[Route('/ecole_vtt/fonctionnement', name: 'school_operating', methods: ['GET'])]
    public function schoolOperating(): Response
    {
        return $this->render('content/school.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('school_operating'),
            'background_color' => 'blue',
        ]);
    }

    #[Route('/ecole_vtt/equipement', name: 'school_equipment', methods: ['GET'])]
    public function schoolEquipment(): Response
    {
        return $this->render('content/school.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('school_equipment'),
            'background_color' => 'green',
        ]);
    }

    #[Route('/ecole_vtt/documentation', name: 'school_documentation', methods: ['GET'])]
    #[IsGranted('DOCUMENTATION_LIST')]
    public function schoolDocumentation(
        DocumentationRepository $documentationRepository,
        DocumentationDtoTransformer $documentationDtoTransformer,
    ): Response {
        return $this->render('content/school.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('school_documentation'),
            'documentations' => $documentationDtoTransformer->fromEntities($documentationRepository->findAllAsc()),
            'background_color' => 'red',
        ]);
    }

    #[Route('/contact', name: 'contact', methods: ['GET', 'POST'])]
    public function contact(
        Request $request,
        IdentityService $identityService,
        MailerService $mailerService,
        UserDtoTransformer $userDtoTransformer,
        MessageService $messageService,
    ): Response {
        /** @var ?User $user */
        $user = $this->getUser();
        $mainContact = (null !== $user)
            ? $identityService->getMainContact($user)
            : null;
        
        $data = ($mainContact)
            ? ['name' => $mainContact->getName(), 'firstName' => $mainContact->getFirstName(), 'email' => $mainContact->getEmail()]
            : null;

        $form = $this->createForm(ContactType::class, $data);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
     
            $userData = ($user)
                ? $userDtoTransformer->fromEntity($user)
                : $data;

            $data['subject'] = 'Message envoyé depuis le site vttevasionludres.fr';
            if ($mailerService->sendMailToClub($data) && $mailerService->sendMailToMember($userData, $data['subject'], $messageService->getMessageByName('EMAIL_FORM_CONTACT'))) {
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

    #[Route('/Annonces/{title}/{filename}', name: 'annonces', methods: ['GET'])]
    #[Route('/Calendrier/{filename}', name: 'calendrier', methods: ['GET'])]
    public function PermanentRedirect(): Response
    {
        return $this->redirectToRoute('home', [], 308);
    }

    #[Route('/{filename}', name: 'apple_touch_icon', methods: ['GET'], requirements:['filename' => 'apple-touch-icon-(?:[a-z0-9-]+).png'])]
    public function appleTouchIcon(ProjectDirService $projectDir, string $filename): Response
    {
        $path = $projectDir->path('logos', $filename);
        if (!file_exists($path)) {
            $path = $projectDir->path('logos', 'apple-touch-icon-72x72.png');
            ;
        }

        return new BinaryFileResponse($path);
    }

    #[Route('/granted/{role}', name: 'is_granted_by_role', methods: ['GET'], options:['expose' => true])]
    public function isGrantedUser(string $role): JsonResponse
    {
        return new JsonResponse(['isGranted' => $this->isGranted($role)]);
    }

    #[Route('/club/diaporama', name: 'club_slideshow', methods: ['GET'])]
    public function slideshow(): Response
    {
        $form = $this->logService->getForm(['entityName' => 'SlideshowImage']);
        return $this->render('content/slideshow.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/club/images', name: 'slideshow_images', methods: ['GET'], options:['expose' => true])]
    #[IsGranted('ROLE_USER')]
    public function slideshowImages(
        SlideshowImageRepository $slideshowImageRepository,
    ): JsonResponse {
        $images = [];
        /** @var User $user */
        $user = $this->getUser();
        $slideShowimageViewedIds = $this->logRepository->findSlideShowimageViewedIds($user);
        /** @var SlideshowImage $image */
        foreach ($slideshowImageRepository->findAll() as $image) {
            $images[] = [
                'url' => $this->generateUrl('slideshow_image', ['filename' => $image->getFilename()]),
                'directory' => $image->getDirectory()->getName(),
                'id' => $image->getId(),
                'novelty' => !in_array($image->getId(), $slideShowimageViewedIds),
            ];
        }

        return new JsonResponse(['images' => $images]);
    }

    #[Route('/club/image/{filename}', name: 'slideshow_image', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function slideshowImage(
        ProjectDirService $projectDir,
        string $filename,
    ): BinaryFileResponse|Response {
        $finder = new Finder();
        $finder->files()->in($projectDir->path('slideshow'))->name($filename)->depth('<= 1');
        if ($finder->hasResults()) {
            $results = iterator_to_array($finder);
            $file = array_shift($results);
            return new BinaryFileResponse($file);
        }

        return new Response(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/club/actualites', name: 'club_summary', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function summary(
        SummaryRepository $summaryRepository,
        SummaryDtoTransformer $summaryDtoTransformer,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $summaryViewedIds = $this->logRepository->findSummaryViewedIds($user);
        $summaries = $summaryRepository->findLatestDesc();
        foreach ($summaries as $summary) {
            $this->logService->write('Summary', $summary->getId(), $user);
        }
        
        return $this->render('content/summary.html.twig', [
            'summaries_by_bike_rides' => $summaryDtoTransformer->fromEntities($summaries, $summaryViewedIds),
        ]);
    }
}
