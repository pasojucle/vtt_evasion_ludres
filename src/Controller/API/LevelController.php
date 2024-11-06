<?php

declare(strict_types=1);

namespace App\Controller\API;

use App\Dto\DtoTransformer\LevelDtoTransformer;
use App\Entity\Level;
use App\Form\Admin\LevelType;
use App\Repository\LevelRepository;
use App\Service\ApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/level', name: 'api_level_')]
class LevelController extends AbstractController
{
    public function __construct(
        private readonly LevelDtoTransformer $transformer,
        private readonly EntityManagerInterface $entityManager,
        private readonly ApiService $api,
    ) {
    }

    #[Route(path: '/list', name: 'list', methods: ['GET'], options: ['expose' => true])]
    public function list(LevelRepository $levelRepository): JsonResponse
    {
        return new JsonResponse([
            'list' => $this->transformer->fromEntities($levelRepository->findAllTypeMember()),
        ]);
    }

    #[Route(path: '/add', name: 'add', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function add(Request $request): JsonResponse
    {
        $level = new Level();
        $form = $this->api->createForm($request, LevelType::class, $level);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($level);
            $this->entityManager->flush();
            return $this->api->responseForm($level, $this->transformer, 'idASC');
        }
        
        return $this->api->renderModal($form, 'Ajouter la compétence', 'Enregistrer');
    }

    #[Route(path: '/edit/{id}', name: 'edit', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function edit(Request $request, Level $level): JsonResponse|Response
    {
        $form = $this->api->createForm($request, LevelType::class, $level);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            return $this->api->responseForm($level, $this->transformer, 'idASC');
        }
        
        return $this->api->renderModal($form, 'Mofifier la compétence', 'Enregistrer');
    }

    #[Route(path: '/delete/{id}', name: 'delete', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function delete(Request $request, Level $level): JsonResponse
    {
        $form = $this->api->createForm($request, FormType::class, $level);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $response = $this->api->responseForm($level, $this->transformer, 'idASC', true);
            $this->entityManager->remove($level);
            $this->entityManager->flush();
            return $response;
        }
        
        $message = sprintf('<p>Etes vous certain de supprimer la compétence ? %s</p>', $level->getContent());
        return $this->api->renderModal($form, 'Supprimer la compétence', 'Supprimer', 'danger', $message);
    }
}
