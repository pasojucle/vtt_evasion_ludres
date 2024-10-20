<?php

declare(strict_types=1);

namespace App\Service;

use ReflectionClass;
use Twig\Environment;
use function Symfony\Component\String\u;
use Symfony\Component\Form\FormInterface;
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

    public function renderModal(?FormInterface $form, string $title, string $submit, ?string $message = null): JsonResponse
    {
        return new JsonResponse([
            'form' => [
                'action' => $form->getConfig()->getAction(),
                'elements' => $this->twig->render('component/modal.html.twig',[
                    'message' => $message,
                    'components' => $this->getComponents($form),
                ]),
                'submit' => $submit,
            ],
            'theme' => sprintf('btn-%s', ($message) ? 'danger' : 'primary'),
            'title' => $title,
        ]);
    }

    public function getComponents(FormInterface $form,): array
    {
        $components = [];
        foreach($form->createView()->getIterator() as $name => $child) {
            if ($component = $this->getComponent($child->vars)) {
                $components[] = $component;
            };
        }
        dump($components);
        return $components;
    }

    private function getComponent(array $vars): ?array
    {
        $props = [
            'id' => $vars['id'],
            'label' => $vars['label'],
            'name' => $vars['full_name'],
            'required' => $vars['required'],
        ];

        $componentPropKeys = [
            'vueChoiceFilter' => ['name' => 'choiceFilterType', 'keys' => ['className', 'field', 'placeholder']], 
            'vueChoiceFiltered' => ['name' => 'choiceFilteredType', 'keys' => ['className', 'exclude']], 
            'vueChoice' => ['name' => 'choiceType', 'keys' => ['className', 'value']],
            'ckeditor' => ['name' => 'ckeditor', 'keys' => ['upload_url', 'toolbar', 'value']],
            'vueText' => ['name' => 'textType', 'keys' => ['value']],
            'hidden' => ['name' => 'hiddenType', 'keys' => ['value']],
        ];
        dump($vars);
        $names = array_intersect($vars['block_prefixes'], array_keys($componentPropKeys));
        $name = ($names) ? $names[array_key_first($names)] : null;
        if ($name) {
            foreach($componentPropKeys[$name]['keys'] as $key) {
                $props[$key] = ('className' === $key) ? U($vars[$key])->snake() : $vars[$key];
            }
            return ['name' => ucfirst($componentPropKeys[$name]['name']), 'props' => $props, 'row_attr' => $vars['row_attr']];
        }
        return null;
    }

    public function createForm(Request $request, string $type, ?object $entity): FormInterface
    {
        return $this->formFactory->create($type, $entity, [
            'action' => $request->getRequestUri(),
        ]);
    }

    public function responseForm(object $entity, DtoTransformerInterface $transformer, string $sort = 'nameASC', bool $isDeleted = false, ?string $className = null): JsonResponse
    {
        return new JsonResponse([
            'success' => true, 
            'data' => [
                'deleted' => $isDeleted,
                'entity' => $className ?? U((new ReflectionClass($entity))->getShortName())->snake(),
                // 'entity' => ($entity instanceof PersistentCollection) 
                //     ? $this->getNameFromCollection($entity)
                //     : U((new ReflectionClass($entity))->getShortName())->snake(),
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