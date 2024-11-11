<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\OrderStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;

class OrderFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', EnumType::class, [
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
