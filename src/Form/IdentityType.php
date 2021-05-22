<?php

namespace App\Form;

use App\Entity\Identity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class IdentityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('birthDate', DateTimeType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'autocomplete' => "off",
                ],
            ])
            ->add('birthplace', TextType::class, [
                'label' => 'Lieux de naissance',
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone fixe',
                'required' => false,
            ])
            ->add('mobile', TextType::class, [
                'label' => 'Téléphone mobile',
            ])
            ->add('profession', TextType::class, [
                'label' => 'Profession',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse mail'
            ])
        ;
        if ('mineur' === $options['type']) {
            $builder
                ->add('kinship', ChoiceType::class, [
                    'label' => 'Parenté',
                    'choices' => array_flip(Identity::KINSHIPS)
                ])
                ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Identity::class,
            'type' => 'adulte',
        ]);
    }
}
