<?php

namespace App\Form;

use App\Entity\Parameter;
use App\Form\ParameterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ParametersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parameters', CollectionType::class, [
                'entry_type' => ParameterType::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }
}
