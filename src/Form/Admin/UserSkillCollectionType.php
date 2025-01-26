<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSkillCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userSkills', CollectionType::class, [
                'label' => false,
                'entry_type' => UserSkillType::class,
                'entry_options' => [
                    'text_type' => $options['text_type'],
                ],
                'block_prefix' => 'ReactCollection',
                'row_attr' => [
                    'class' => 'col-md-12',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'text_type' => UserSkillType::BY_USERS,
        ]);
    }
}
