<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\OrderLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', QuantityType::class, [
                'label' => false,
                'row_attr' => [
                    'class' => 'form-group',
                ],
                'attr' => [
                    'class' => 'orderline-quantity',
                    'min' => 1,
                ],
            ])
            ->add('remove', SubmitType::class, [
                'label' => 'supprimer',
                'label_html' => true,
                'attr' => [
                    'class' => 'orderline-remove',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderLine::class,
        ]);
    }
}
