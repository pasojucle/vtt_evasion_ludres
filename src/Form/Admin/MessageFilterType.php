<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\ParameterGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                'attr' => [
                    'data-controller' => "filter",
                    'data-action' => 'change->filter#change'
                ],
                'required' => false,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'issues' => [],
            'attr' => [
                'data-controller' => "filter",
            ],
        ]);
    }
}
