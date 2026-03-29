<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Dto\DtoTransformer\SkillDtoTransformer;
use App\Entity\Skill;
use App\Form\Admin\SkillFilterType;
use App\Form\Admin\SkillType;
use App\Repository\SkillRepository;
use App\Service\ExportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[Route(path: '/admin/skill', name: 'admin_skill_')]
class SkillController extends AbstractController
{
    public function __construct(
        private SkillRepository $skillRepository,
        private SkillDtoTransformer $skillDtoTransformer,
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/list', name: 'list', methods: ['GET'])]
    #[IsGranted('SKILL_LIST')]
    public function list(
        Request $request,
    ): Response {
        $form = $this->createForm(SkillFilterType::class);
        $form->handleRequest($request);

        $skills = $this->skillRepository->findFiltered(
            $form->get('skillCategory')->getData()?->getId(),
            $form->get('level')->getData()?->getId(),
        );

        return $this->render('skill/admin/list.html.twig', [
            'settings' => [
                'parameters' => [],
                'routes' => [
                    ['name' => 'admin_skill_category_list', 'label' => 'Catégories'],
                ],
            ],
            'skills' => $this->skillDtoTransformer->fromEntities($skills),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/export', name: 'export', methods: ['GET'])]
    #[IsGranted('SKILL_LIST')]
    public function adminOrderHeadersExport(
        ExportService $exportService,
        SkillDtoTransformer $skillDtoTransformer,
    ): Response {
        $skills = $this->skillRepository->findAll();
        $content = $exportService->exportSkills($skillDtoTransformer->fromEntities($skills));

        $response = new Response($content);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'export_competences.csv'
        );
        
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('/autocomplete', name: 'autocomplete', methods: ['GET', 'POST'])]
    #[IsGranted('IS_MEMBER')]
    public function adminSkillAutocomplete(
        Request $request
    ): JsonResponse {
        $categoryId = $request->query->has('category') ? $request->query->getInt('category') : null;
        $levelId = $request->query->has('level') ? $request->query->getInt('level') : null;
        $clusterId = $request->query->has('cluster') ? $request->query->getInt('cluster') : null;

        $skills = $this->skillRepository->findFiltered($categoryId, $levelId, $clusterId);

        $results = array_map(fn (Skill $skill) => [
            'value' => $skill->getId(),
            'text' => $skill->getContent(),
        ], $skills);

        return new JsonResponse(['results' => $results]);
    }

    #[Route(path: '/add', name: 'add', methods: ['GET', 'POST'])]
    #[IsGranted('SKILL_ADD')]
    public function add(Request $request): Response
    {
        $queryParams = $request->query->all();
        $skill = new Skill();
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(SkillType::class, $skill, [
            'action' => $request->getUri(),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $skill = $form->getData();
                $this->entityManager->persist($skill);
                $this->entityManager->flush();

                return $this->redirectToRoute('admin_skill_list', $queryParams);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('skill/admin/skill_edit.modal.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter une compétence',
            'btn' => ['label' => 'Ajouter', 'icon' => 'lucide:plus'],
        ], $response);
    }

    #[Route(path: '/edit/{skill}', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('SKILL_EDIT', 'skill')]
    public function edit(
        Request $request,
        Skill $skill
    ): Response {
        $queryParams = $request->query->all();
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(SkillType::class, $skill, [
            'action' => $request->getUri(),
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $skill = $form->getData();
                $this->entityManager->flush();

                return $this->redirectToRoute('admin_skill_list', $queryParams);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->render('skill/admin/skill_edit.modal.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier une compétence',
            'btn' => ['label' => 'Modifier', 'icon' => 'lucide:pen'],
        ], $response);
    }

    #[Route(path: '/delete/{skill}', name: 'delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    #[IsGranted('SKILL_EDIT', 'skill')]
    public function delete(Request $request, Skill $skill): Response
    {
        $queryParams = $request->query->all();
        $response = new Response("OK", Response::HTTP_OK);
        $form = $this->createForm(FormType::class, null, [
            'action' => $request->getUri(),
            'attr' => ['data-action' => 'turbo:submit-end->modal#handleFormSubmit']
        ]);

        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $skillId = $skill->getId();
                $this->entityManager->remove($skill);
                $this->entityManager->flush();
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                    return $this->render('cluster/admin/skill_deleted.stream.html.twig', [
                        'skillId' => $skillId,
                    ]);
                }

                return $this->redirectToRoute('admin_skill_list', $queryParams);
            }
            $response = new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        return $this->render('component/destructive.modal.html.twig', [
            'title' => 'Supprimer une compétence',
            'content' => sprintf('Etes vous certain de supprimer la compétence %s ?', $skill->getContent()),
            'btn_label' => 'Supprimer',
            'form' => $form->createView(),
        ], $response);
    }
}
