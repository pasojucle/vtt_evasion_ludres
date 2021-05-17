<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class QsSportValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value', ChoiceType::class, [
                'label' => false,
                'choices' => [
                    'oui' => true,
                    'non' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
        ;
    }
}