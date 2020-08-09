<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

            $builder
                ->add('term', TextType::class, [
                    'attr' => [
                        'placeholder' => 'Rechercher',
                    ],
                ])
                ;
    }

    /*public function configureOptions(OptionsResolver $resolver)
    {

    }*/
}
