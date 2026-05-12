<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Service\Filter\FilterFieldConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterAdvancedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['advanced_fields'] as $fieldConfig) {
            /** @var FilterFieldConfig $fieldConfig */
            $builder->add(
                $fieldConfig->name,
                $fieldConfig->type,
                $fieldConfig->options
            );
        }
        foreach ($options['fields'] as $fieldConfig) {
            /** @var FilterFieldConfig $fieldConfig */
            $builder->add(
                $fieldConfig->name,
                $fieldConfig->hiddenType,
                $fieldConfig->hiddenOptions
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'fields' => [],
            'advanced_fields' => [],
            'csrf_protection' => false,
            'attr' => [
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
