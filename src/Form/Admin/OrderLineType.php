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
                'data-action' => 'change->form-modifier#change',
                'data-container-id' => sprintF('order-update-%s', $options['order_id']),
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
                ])
                ->add('state', EnumType::class, [
                    'label' => false,
                    'class' => OrderLineStateEnum::class,
                    'expanded' => true,
                    'block_prefix' => 'ballot_radio',
                    'choice_attr' => function ($choice, string $key, mixed $value) use ($attrClass) {
                        return array_merge($attrClass, [
                            'data-variant' => $choice->variant()->value, 
                            'data-icon' => $choice->icon(), 
                        ]);
                    }
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderLine::class,
            'order_id' => 0,
            'order_status' => OrderStatusEnum::IN_PROGRESS,
        ]);
    }
}
