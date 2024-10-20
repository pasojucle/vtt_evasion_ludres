<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Level;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Form\Type\VueChoiceFilterType;
use App\Form\Type\VueChoiceFilteredType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ClusterSkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('skillCategory', VueChoiceFilterType::class, [
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
            ->add('level', VueChoiceFilterType::class, [
                'label' => 'Niveau',
                'class' => Level::class,
                'placeholder' => 'Séléctionner un niveau',
                'field' => 'category',
                'mapped' => false,
                'required' => false,
                'row_attr' => [
                    'class' => 'col-md-6',
                ],
            ])
            ->add('skill', VueChoiceFilteredType::class, [
                'label' => 'Compétences',
                'class' => Skill::class,
                'exclude' => 'cluster_skill',
                'row_attr' => [
                    'class' => 'col-md-12',
                ],
            ])
        ;
    }
}
