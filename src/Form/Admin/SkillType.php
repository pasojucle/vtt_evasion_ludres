<?php

namespace App\Form\Admin;

use App\Entity\Level;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Form\Type\CkeditorType;
use App\Form\Type\VueChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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
                    'class' => 'col-md-12',
                ],
            ])
            ->add('category', VueChoiceType::class, [
                'label' => 'Catégorie',
                'class' => SkillCategory::class,
                'choice_label' => 'name',
                'row_attr' => [
                    'class' => 'col-md-12',
                ],
            ])
            ->add('level', VueChoiceType::class, [
                'label' => 'Niveau',
                'class' => Level::class,
                'row_attr' => [
                    'class' => 'col-md-12',
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
