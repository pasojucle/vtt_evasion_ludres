<?php

declare(strict_types=1);

namespace App\Service;

use ReflectionClass;
use Twig\Environment;
use function Symfony\Component\String\u;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Dto\DtoTransformer\DtoTransformerInterface;
use Doctrine\ORM\PersistentCollection;

class ApiService
{
    public function __construct(
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
    )
    {
        
    }

    public function renderModal(FormInterface $form, string $title, string $submit, ?string $message = null, ?array $components = null): JsonResponse
    {
        return new JsonResponse([
            'form' => [
                'action' => $form->getConfig()->getAction(),
                'elements' => $this->twig->render('component/modal.html.twig',[
                    'form' => $form->createView(),
                    'message' => $message,
                    'components' => $components,
                ]),
                'submit' => $submit,
            ],
            'theme' => sprintf('btn-%s', ($message) ? 'danger' : 'primary'),
            'title' => $title,
        ]);
    }

    public function createForm(Request $request, string $type, ?object $entity): FormInterface
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
                'entity' => ($entity instanceof PersistentCollection) 
                    ? $this->getNameFromCollection($entity)
                    : U((new ReflectionClass($entity))->getShortName())->snake(),
                'value' => $transformer->fromEntity($entity),
                'sort' => $sort,
            ],
        ]);
    }

    private function getNameFromCollection(PersistentCollection $collection): string
    {
        /** @var AssociationMapping&ToManyAssociationMapping $mapping */
        $mapping = $collection->getMapping();
        return $mapping->joinTable->name;
    }
}