<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\Enum\ActivityPeriod;
use App\Dto\Filter\ActivityFilter;
use App\Form\EventListener\ActivityFilterSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('period', EnumType::class, [
                'label' => false,
                'class' => ActivityPeriod::class,
                'placeholder' => 'Tous',
                'required' => false,
                'attr' => [
                    'data-controller' => "filter",
                    'data-action' => 'change->filter#change'
                ],
            ])
            ->addEventSubscriber(new ActivityFilterSubscriber());
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ActivityFilter::class,
            'attr' => [
                'data-controller' => "filter",
            ],
        ]);
    }
}
