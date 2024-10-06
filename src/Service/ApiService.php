<?php

declare(strict_types=1);

namespace App\Service;

use ReflectionClass;
use Twig\Environment;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Dto\DtoTransformer\DtoTransformerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use function Symfony\Component\String\u;

class ApiService
{
    public function __construct(
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
    )
    {
        
    }

    public function renderModal(FormInterface $form, string $title, string $submit, ?string $message = null): JsonResponse
    {
        return new JsonResponse([
            'form' => [
                'action' => $form->getConfig()->getAction(),
                'elements' => $this->twig->render('component/modal.html.twig',[
                    'form' => $form->createView(),
                    'message' => $message,
                ]),
                'submit' => $submit,
            ],
            'theme' => sprintf('btn-%s', ($message) ? 'danger' : 'primary'),
            'title' => $title,
        ]);
    }

    public function createForm(Request $request, string $type, object $entity): FormInterface
    {
        return $this->formFactory->create($type, $entity, [
            'action' => $request->getRequestUri(),
        ]);
    }

    public function responseForm(object $entity, DtoTransformerInterface $transformer, string $sort = 'nameASC', bool $isDeleted = false ): JsonResponse
    {
        return new JsonResponse([
            'success' => true, 
            'data' => [
                'deleted' => $isDeleted,
                'entity' => U((new ReflectionClass($entity))->getShortName())->snake(),
                'value' => $transformer->fromEntity($entity),
                'sort' => $sort,
            ],
        ]);
    }
}