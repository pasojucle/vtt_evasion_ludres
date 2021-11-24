<?php

namespace App\Form;

use App\Form\ParameterType;
use App\Entity\ParameterGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ParameterGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('parameters', CollectionType::class, [
            'label' => false,
            'entry_type' => ParameterType::class,
            'entry_options' => [
                'label' => false,
            ],
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ParameterGroup::class,
            
        ]);
    }
}
