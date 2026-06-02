<?php

declare(strict_types=1);

namespace App\Form\Filter;

use App\Dto\Filter\AbstractFilter;
use App\Service\Filter\FilterFieldConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FilterAdvancedType extends AbstractType
{
        public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) 
    {

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
            'data_class' => null,
            'fields' => [],
            'advanced_fields' => [],
            'csrf_protection' => false,
            'attr' => [
                'data-filter-target' => "form",
                'data-controller' => "filter",
                'data-turbo-frame' => '_top',
                'data-action' => 'turbo:submit-end->sheet#handleFormSubmit'
                ],
        ]);

        $resolver->setAllowedTypes('fields', 'array');

        $resolver->setAllowedTypes('advanced_fields', 'array');
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
