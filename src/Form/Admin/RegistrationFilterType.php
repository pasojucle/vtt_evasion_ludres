<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('isYearly', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Tous',
                'choices' => [
                    '3 séances d\'essai' => 0,
                    'Inscription' => 1,
                ],
                'attr' => [
                    'class' => 'btn',
                ],
                'required' => false,
            ])
        ;
    }
}
