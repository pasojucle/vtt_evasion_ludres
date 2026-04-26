<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Dto\Filter\OrderFilter;
use App\Entity\Enum\OrderStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('status', EnumType::class, [
                'label' => false,
                'placeholder' => 'Tous',
                'class' => OrderStatusEnum::class,
                'attr' => [
                    'class' => 'btn',
                    'data-controller' => "filter",
                    'data-action' => 'change->filter#change'
                ],
                'required' => false,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderFilter::class,
            'attr' => [
                'data-controller' => "filter",
            ],
        ]);
    }
}
