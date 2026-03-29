<?php

namespace App\Form\Admin;

use App\Form\Admin\MemberSkillType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberSkillCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('memberSkills', CollectionType::class, [
                'label' => false,
                'entry_type' => MemberSkillType::class,
                'entry_options' => [
                    'text_type' => $options['text_type'],
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'text_type' => MemberSkillType::BY_USERS,
            // 'attr' => [
            //     'data-controller' => "form-modifier",
            // ],
            'attr' => [
                'data-controller' => 'form-filter',
                'data-turbo-frame' => 'member_skills_list_frame',
            ]
        ]);
    }
}
