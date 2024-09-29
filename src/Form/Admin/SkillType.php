<?php

namespace App\Form\Admin;

use App\Entity\Level;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use Symfony\Component\Form\AbstractType;
use App\Form\Type\CkeditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', CKEditorType::class, [
                'label' => 'Descriptif',
                'config_name' => 'base',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => SkillCategory::class,
                'choice_label' => 'name',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('level', EntityType::class, [
                'label' => 'Niveaux',
                'class' => Level::class,
                'choice_label' => 'title',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Skill::class,
        ]);
    }
}
