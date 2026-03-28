<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Level;
use App\Entity\SkillCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkillFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('skillCategory', EntityType::class, [
                'label' => 'Categorie',
                'class' => SkillCategory::class,
                'placeholder' => 'Séléctionner une catégorie',
                'required' => false,
                'autocomplete' => true,
                'row_attr' => [
                    'class' => 'form-group-inline',
                    'data-action' => 'change->form-filter#submit',
                ],
            ])
            ->add('level', EntityType::class, [
                'label' => 'Niveau',
                'class' => Level::class,
                'placeholder' => 'Séléctionner un niveau',
                'required' => false,
                'autocomplete' => true,
                'row_attr' => [
                    'class' => 'form-group-inline',
                    'data-action' => 'change->form-filter#submit',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'attr' => [
                'data-controller' => 'form-filter',
                'data-turbo-frame' => 'skills-list-frame',
                'data-turbo-action' => 'advance',
            ]
        ]);
    }
}
