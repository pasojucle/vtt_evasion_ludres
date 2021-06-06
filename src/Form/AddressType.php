<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $required = empty($options['row_class']);
        $builder
            ->add('street', TextType::class, [
                'label' => 'Adresse',
                'row_attr' => [
                    'class' => 'form-group-inline full-width'.$options['row_class'],
                ],
                'required' => $required,
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'constraints' => [new Length(['min' => 5, 'max' => 5])],
                'row_attr' => [
                    'class' => 'form-group-inline'.$options['row_class'],
                ],
                'required' => $required,
            ])
            ->add('town', TextType::class, [
                'label' => 'Ville',
                'row_attr' => [
                    'class' => 'form-group-inline'.$options['row_class'],
                ],
                'required' => $required,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'row_class' => '',
        ]);
    }
}
