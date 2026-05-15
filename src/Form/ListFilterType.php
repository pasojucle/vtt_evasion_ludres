<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\Filter\ActivityFilter;
use App\Form\EventListener\ActivityFilterSubscriber;
use App\Service\Filter\FilterFieldConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['fields'] as $fieldConfig) {
            if (!$fieldConfig->isSubscriberFlield) {
                /** @var FilterFieldConfig $fieldConfig */
                $builder->add(
                    $fieldConfig->name,
                    $fieldConfig->type,
                    $fieldConfig->options
                );
            }
        }

        $builder ->setMethod('GET');
        
        if ($options['event_subscriber']) {
            $builder->addEventSubscriber($options['event_subscriber']);
        }
            
        foreach ($options['advanced_fields'] as $fieldConfig) {
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
            'data_class' => ActivityFilter::class,
            'fields' => [],
            'advanced_fields' => [],
            'event_subscriber' => null,
            'csrf_protection' => false,
            'attr' => [
                'data-controller' => "filter",
                'data-turbo-frame' => '_top',
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
