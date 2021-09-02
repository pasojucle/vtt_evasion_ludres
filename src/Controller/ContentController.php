<?php

namespace App\Controller;

use App\Entity\Link;
use App\Entity\Content;
use App\Form\ContactType;
use App\Form\ContentType;
use App\Service\MailerService;
use App\Service\OrderByService;
use App\Service\PaginatorService;
use App\Repository\LinkRepository;
use App\Repository\EventRepository;
use App\Repository\LevelRepository;
use App\Repository\ContentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContentController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private OrderByService $orderByService;
    private ContentRepository $contentRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        OrderByService $orderByService,
        ContentRepository $contentRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->orderByService = $orderByService;
        $this->contentRepository = $contentRepository;
    }
    /**
     * @Route("/admin/page/accueil/contenus/{isFlash}", name="admin_home_contents", defaults={"route"="home", "isFlash"=true})
     * @Route("/admin/contenus", name="admin_contents", defaults={"route"=null, "isFlash"=false})
     */
    public function list(
        PaginatorService $paginator,
        Request $request,
        ?string $route,
        bool $isFlash
    ): Response
    {
        $query =  $this->contentRepository->findContentQuery($route, $isFlash);
        $contents =  $paginator->paginate($query, $request, PaginatorService::PAGINATOR_PER_PAGE);

        return $this->render('content/admin/list.html.twig', [
            'contents' => $contents,
            'lastPage' => $paginator->lastPage($contents),
            'current_route' => $route,
            'is_flash' => $isFlash,
            'current_filters' => ['route' => $route, 'isFlash' => $isFlash],
        ]);
    }


    /**
     * @Route("/admin/page/accueil/contenu/{content}", name="admin_home_content_edit", defaults={"content"=null})
     * @Route("/admin/contenu/{content}", name="admin_content_edit")
     */
    public function adminContentEdit(
        Request $request,
        ?Content $content
    ): Response
    {
        $form = $this->createForm(ContentType::class, $content);
        
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $content = $form->getData();
            $content->setOrderBy(0);
            $order = $this->contentRepository->findNexOrderByRoute($content->getRoute(), $content->isFlash());
            $content->setOrderBy($order);
            $this->entityManager->persist($content);
            $this->entityManager->flush();

            if ('home' === $content->getRoute()) {
                $contents = $this->contentRepository->findByRoute('home', !$content->isFlash());
                $this->orderByService->resetOrders($contents);

                return $this->redirectToRoute('admin_home_contents', ['route' => $content->getRoute(), 'isFlash' => (int) $content->isFlash()]);
            }

            return $this->redirectToRoute('admin_contents');
        }

        return $this->render('content/admin/edit.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/supprimer/contenu/{content}", name="admin_content_delete")
     */
    public function adminContentDelete(
        Request $request,
        Content $content
    ): Response
    {
        $form = $this->createForm(FormType::class, null, [
            'action' => $this->generateUrl('admin_content_delete', 
                [
                    'content'=> $content->getId(),
                ]
            ),
        ]);
        $route = $content->getRoute();
        $isFlash = $content->IsFlash();

        $form->handleRequest($request);
        if ($request->isMethod('post') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($content);
            $this->entityManager->flush();

            $contents = $this->contentRepository->findByRoute($route, $isFlash);
            $this->orderByService->ResetOrders($contents);

            return $this->redirectToRoute('admin_contents', ['route' => $route, 'isFlash' => $isFlash]);
        }

        return $this->render('content/admin/delete.modal.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/ordonner/contenu/{content}", name="admin_content_order", options={"expose"=true},)
     */
    public function adminContentOrder(
        Request $request,
        Content $content
    ): Response
    {
        $route = $content->getRoute();
        $isFlash = $content->isFlash();
        $newOrder = $request->request->get('newOrder');
        $contents = $this->contentRepository->findByRoute($route, $isFlash);

        $this->orderByService->setNewOrders($content, $contents, $newOrder);

        return new Response();
    }

    /**
     * @Route("/", name="home")
     */
    public function home (
        LinkRepository $linkRepository,
        ContentRepository $contentRepository,
        EventRepository $eventRepository
    ): Response
    {
        $homeContents = $contentRepository->findHomeContents();
        $linksBikeRide = $linkRepository->findByPosition(Link::POSITION_HOME_BIKE_RIDE);
        $linksFooter = $linkRepository->findByPosition(Link::POSITION_HOME_FOOTER);
        $events = $eventRepository->findEnableView();

        return $this->render('content/home.html.twig', [
            'links_bike_ride' => $linksBikeRide,
            'links_footer' => $linksFooter,
            'events' => $events,
            'home_contents' => $homeContents,
        ]);
    }

     /**
     * @Route("/club", name="club")
     */
    public function club(): Response
    {

        return $this->render('content/club.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('club'),
        ]);
    }

    /**
     * @Route("/ecole_vtt/disciplines", name="school_practices")
     */
    public function schoolPractices(
        LevelRepository $levelRepository
    ): Response
    {

        return $this->render('content/school.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('school_practices'),
            'levels' => $levelRepository->findAllTypeMemberNotProtected(),
            'background_color' => 'red',
            'background_img' => 'ecole_vtt_disciplines.jpg',
        ]);
    }

    /**
     * @Route("/ecole_vtt/presentation", name="school_overview")
     */
    public function schoolOverview(
        LevelRepository $levelRepository
    ): Response
    {

        return $this->render('content/school.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('school_overview'),
            'background_color' => 'green',
            'background_img' => 'ecole_vtt_groupe.jpg',
        ]);
    }

    /**
     * @Route("/ecole_vtt/fonctionnement", name="school_operating")
     */
    public function schoolOperating(
        LevelRepository $levelRepository
    ): Response
    {

        return $this->render('content/school.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('school_operating'),
            'background_color' => 'blue',
            'background_img' => 'ecole_vtt_fonctionnement.jpg',
        ]);
    }
    

    /**
     * @Route("/ecole_vtt/equipement", name="school_equipment")
     */
    public function schoolEquipment(
        LevelRepository $levelRepository
    ): Response
    {

        return $this->render('content/school.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('school_equipment'),
            'background_color' => 'green',
            'background_img' => 'ecole_vtt_equipement.jpg',
        ]);
    }
    
    /**
     * @Route("/contact", name="contact")
     */
    public function contact(
        Request $request,
        MailerService $mailerService
    ): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data['subject'] = 'Message envoyé depuis le site vttevasionludres.fr';
            if ($mailerService->sendMailToClub($data) && $mailerService->sendMailToMember($data)) {
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

    /**
     * @Route("/reglement", name="rules")
     */
    public function rules(): Response
    {
        return $this->render('content/rules.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('rules'),
        ]);
    }

    /**
     * @Route("/mentions/legales", name="legal_notices")
     */
    public function legalNotices(): Response
    {
        return $this->render('content/legal_notices.html.twig', [
            'content' => $this->contentRepository->findOneByRoute('legal_notices'),
        ]);
    }
}
