<?php

namespace App\Form\Admin;



use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UserSkillCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('collection', CollectionType::class, [
                'label' => false,
                'entry_type' => UserSkillType::class,
                'block_prefix' => 'vueCollection',
            ])
        ;
        
    }
}
