<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\ParameterGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MessageFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('section', EntityType::class, [
                'label' => false,
                'placeholder' => 'Tous',
                'class' => ParameterGroup::class,
                'choice_label' => 'label',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'required' => false,
            ])
            ;
    }
}
