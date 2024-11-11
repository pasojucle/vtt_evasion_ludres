<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\DtoTransformer\DtoTransformerInterface;
use Doctrine\ORM\PersistentCollection;
use ReflectionClass;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use function Symfony\Component\String\u;

class ApiService
{
    private const COMPONENT_PROP_KEYS = [
        'vueChoiceFilter' => ['name' => 'ChoiceFilterType', 'keys' => ['className', 'field', 'placeholder']],
        'vueChoiceFiltered' => ['name' => 'ChoiceFilteredType', 'keys' => ['className', 'exclude']],
        'vueChoice' => ['name' => 'ChoiceType', 'keys' => ['className', 'value']],
        'ckeditor' => ['name' => 'Ckeditor', 'keys' => ['upload_url', 'toolbar', 'value']],
        'vueText' => ['name' => 'TextType', 'keys' => ['value', 'disabled']],
        'collection' => ['name' => 'CollectionType', 'keys' => []],
        'vueRadio' => ['name' => 'RadioType', 'keys' => ['value', 'choices']],
        'hidden' => ['name' => 'hiddenType', 'keys' => ['value']],
    ];

    public function __construct(
        private readonly Environment $twig,
        private readonly FormFactoryInterface $formFactory,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function renderModal(FormInterface $form, string $title, string $submit, string $theme = 'primary', ?string $message = null): JsonResponse
    {
        return new JsonResponse([
            'form' => [
                'action' => $form->getConfig()->getAction(),
                'elements' => $this->twig->render('component/modal.html.twig', [
                    'message' => $message,
                    'components' => $this->getComponents($form),
                ]),
                'submit' => $submit,
            ],
            'theme' => sprintf('btn-%s', $theme),
            'title' => $title,
        ]);
    }

    public function getComponents(FormInterface $form): array
    {
        $components = [];
        foreach ($form->createView()->getIterator() as $child) {
            $blockPrefix = $this->getBlockPrefix($child);
            if ($component = $this->getComponent($child, $blockPrefix, $this->getChildren($child, $blockPrefix))) {
                $components[] = $component;
            };
        }

        return $components;
    }

    public function getComponent(FormView $form, string $blockPrefix, array $children = []): ?array
    {
        if ($blockPrefix) {
            $props = $this->getProps($form->vars, $blockPrefix);
            return [
                'id' => $form->vars['id'],
                'name' => $this->getComponentName($blockPrefix),
                'props' => $props,
                'row_attr' => $form->vars['row_attr'],
                'children' => $children,
            ];
        }
        return null;
    }

    private function getChildren(FormView $form, string $blockPrefix): array
    {
        $children = [];
        if ('collection' === $blockPrefix) {
            foreach ($form->children as $entryKey => $entry) {
                foreach ($entry->children as $entryChild) {
                    if ($entryChildBlocPrefix = $this->getBlockPrefix($entryChild)) {
                        $children[$entry->vars['name']][] = [
                            'name' => $this->getComponentName($entryChildBlocPrefix),
                            'props' => $this->getProps($entryChild->vars, $entryChildBlocPrefix, $entryKey),
                            'row_attr' => $entryChild->vars['row_attr'],
                        ];
                    }
                }
            }
        }

        return $children;
    }

    public function getBlockPrefix(FormView $form): ?string
    {
        $blockPrefixes = array_intersect($form->vars['block_prefixes'], array_keys(self::COMPONENT_PROP_KEYS));
        return ($blockPrefixes) ? $blockPrefixes[array_key_first($blockPrefixes)] : null;
    }

    public function getComponentName(string $blockPrefixe): string
    {
        return ucfirst(self::COMPONENT_PROP_KEYS[$blockPrefixe]['name']);
    }

    public function getProps(array $vars, string $blockPrefix, ?int $entryKey = null): array
    {
        $props = [
            'id' => $vars['id'],
            'label' => $vars['label'],
            'name' => $vars['full_name'],
            'required' => $vars['required'],
        ];
        foreach (self::COMPONENT_PROP_KEYS[$blockPrefix]['keys'] as $key) {
            $props[$key] = match ($key) {
                'className' => U($vars[$key])->snake(),
                'choices' => $this->enumChoicesToArray($vars[$key], $entryKey),
                default => $vars[$key],
            };
        }
        return $props;
    }

    private function enumChoicesToArray(array $enumChoices, int $entryKey): array
    {
        $choices = [];
        foreach ($enumChoices as $choice) {
            $enum = $choice->data;
            $choices[] = [
                'id' => sprintf('%s_%s', $enum->name, $entryKey),
                'name' => $enum->name,
                'value' => $enum->value,
                'label' => ucfirst($enum->trans($this->translator)),
                'color' => $enum->color(),
            ];
        }

        return $choices;
    }

    public function createForm(Request $request, string $type, null|array|object $entity, array $params = []): FormInterface
    {
        $params['action'] = $request->getRequestUri();
        return $this->formFactory->create($type, $entity, $params);
    }

    public function responseForm(object $entity, DtoTransformerInterface $transformer, string $sort = 'nameASC', bool $isDeleted = false, ?string $className = null): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => [
                'deleted' => $isDeleted,
                'entity' => $className ?? U((new ReflectionClass($entity))->getShortName())->snake(),
                'value' => $transformer->fromEntity($entity),
                'sort' => $sort,
            ],
        ]);
    }
}
