<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\OrderLineStateEnum;
use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderLine;
use App\Entity\Size;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (OrderStatusEnum::ORDERED === $options['order_status']) {
            $attrClass = [
                'class' => 'form-modifier',
                'data-modifier' => 'order',
            ];

            $builder
                ->add('size', EntityType::class, [
                    'label' => 'Taille',
                    'class' => Size::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('s');
                    },
                    'choice_label' => 'name',
                    'expanded' => false,
                    'multiple' => false,
                    'row_attr' => [
                        'class' => 'form-group-inline form-group-small',
                    ],
                ])
                ->add('state', EnumType::class, [
                    'label' => false,
                    'class' => OrderLineStateEnum::class,
                    'expanded' => true,
                    'block_prefix' => 'btn_radio',
                    'choice_attr' => function ($choice, string $key, mixed $value) use ($attrClass) {
                        $appearance = $choice->getAppearance();
                        return array_merge($attrClass, ['data-color' => $appearance['class'], 'data-icon' => $appearance['icon'], ]);
                    }
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderLine::class,
            'order_status' => OrderStatusEnum::IN_PROGRESS,
        ]);
    }
}
