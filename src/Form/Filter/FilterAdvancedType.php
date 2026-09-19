<?php

declare(strict_types=1);

namespace App\Form\Filter;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Filter\FilterFieldConfig;
use App\Dto\Filter\AbstractFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FilterAdvancedType extends AbstractType
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var AbstractFilter $data */
            $data = $event->getData();
            $options = $form->getConfig()->getOptions();
            foreach ($options['advanced_fields'] as $fieldConfig) {
                /** @var FilterFieldConfig $fieldConfig */
                $form->add(
                    $fieldConfig->name,
                    $fieldConfig->type,
                    $this->generateAutocompleteUrl($fieldConfig->options, $data->AllowedtoArray($fieldConfig->allowedFilterNames))
                );
            }
        });

        foreach ($options['fields'] as $fieldConfig) {
            /** @var FilterFieldConfig $fieldConfig */
            $builder->add(
                $fieldConfig->name,
                $fieldConfig->hiddenType,
                $fieldConfig->hiddenOptions
            );
        }

        if ($options['isPaginated']) {
            $builder->add('page', HiddenType::class);
        }
    }

    private function generateAutocompleteUrl(array $options, array $filters): array
    {
        if (array_key_exists('autocomplete_url', $options)) {
            $route = $options['autocomplete_url'];
            $options['autocomplete_url'] = $this->urlGenerator->generate($route, $filters);
        }

        return $options;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'filter_config' => null,
            'data_class' => null,
            'fields' => [],
            'advanced_fields' => [],
            'isPaginated' => true,
            'csrf_protection' => false,
            'attr' => [
                'data-filter-target' => "form",
                'data-controller' => "filter",
                'data-turbo-frame' => '_top',
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit'
            ],
        ]);

        $resolver->setAllowedTypes('filter_config', ['null', FilterConfigInterface::class]);
        $resolver->setAllowedTypes('fields', 'array');
        $resolver->setAllowedTypes('advanced_fields', 'array');

        $resolver->setNormalizer('data_class', function (Options $options, ?string $value): ?string {
            if ($options['filter_config'] instanceof FilterConfigInterface) {
                return $options['filter_config']->getDataClass() ?? $value;
            }

            return $value;
        });

        $resolver->setNormalizer('isPaginated', function (Options $options, bool $value): bool {
            if ($options['filter_config'] instanceof FilterConfigInterface) {
                return $options['filter_config']->isPaginated();
            }

            return $value;
        });

        $resolver->setNormalizer('fields', function (Options $options, array $value): array {
            if ($options['filter_config'] instanceof FilterConfigInterface && empty($value)) {
                return $options['filter_config']->getFields();
            }

            return $value;
        });

        $resolver->setNormalizer('advanced_fields', function (Options $options, array $value): array {
            if ($options['filter_config'] instanceof FilterConfigInterface && empty($value)) {
                return $options['filter_config']->getAdvancedFields();
            }

            return $value;
        });
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
