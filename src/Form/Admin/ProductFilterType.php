<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Dto\Enum\ProductStateEnum;
use App\Dto\Filter\ProductFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('state', EnumType::class, [
                'label' => false,
                'placeholder' => 'Tous',
                'class' => ProductStateEnum::class,
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
            'data_class' => ProductFilter::class,
        ]);
    }
}
