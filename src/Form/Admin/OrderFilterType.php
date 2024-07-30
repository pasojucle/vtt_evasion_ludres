<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\OrderStatusEnum;
use App\Form\Type\OrderStatusEnumType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class OrderFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', OrderStatusEnumType::class, [
                'label' => false,
                'placeholder' => 'Tous',
                'class' => OrderStatusEnum::class,
                'attr' => [
                    'class' => 'btn',
                ],
                'required' => false,
            ])
            ;
    }
}
