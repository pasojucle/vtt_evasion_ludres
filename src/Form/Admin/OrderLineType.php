<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Enum\OrderStatusEnum;
use App\Entity\OrderLine;
use App\Entity\Size;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                ->add('available', ChoiceType::class, [
                    'label' => false,
                    'expanded' => true,
                    'block_prefix' => 'btn_radio',
                    'choices' => [
                        'En stock' => true,
                        'En commande' => false,
                    ],
                    'choice_attr' => [
                        'En stock' => array_merge($attrClass, ['data-color' => 'success-color', 'data-icon' => 'check']),
                        'En commande' => array_merge($attrClass, ['data-color' => 'danger-color', 'data-icon' => 'times']),
                    ],
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
