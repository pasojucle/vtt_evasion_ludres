<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Level;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Form\Type\ReactAutocompleteFilterType;
use App\Form\Type\ReactAutocompleteType;
use App\Form\Type\ReactChoiceFilteredType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkillAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('skillCategory', ReactAutocompleteFilterType::class, [
                'label' => 'Categorie',
                'class' => SkillCategory::class,
                'placeholder' => 'Séléctionner une catégorie',
                'field' => 'category',
                'mapped' => false,
                'required' => false,
                'row_attr' => [
                    'class' => 'col-md-6',
                ],
            ])
            ->add('level', ReactAutocompleteFilterType::class, [
                'label' => 'Niveau',
                'class' => Level::class,
                'placeholder' => 'Séléctionner un niveau',
                'field' => 'level',
                'mapped' => false,
                'required' => false,
                'row_attr' => [
                    'class' => 'col-md-6',
                ],
            ])
            ->add('skill', ReactChoiceFilteredType::class, [
                'label' => 'Compétences',
                'class' => Skill::class,
                'selected_values' => $options['selected_values'],
                'row_attr' => [
                    'class' => 'col-md-12',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'selected_values' => null,
        ]);
    }
}
