<?php

namespace App\Form\Admin;

use App\Entity\OrderHeader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class OrderFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Tous',
                'choices' => array_flip(OrderHeader::STATUS),
                'attr' => [
                    'class' => 'btn',
                ],
                'required' => false,
            ])
            ;
    }
}