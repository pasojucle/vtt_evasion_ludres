<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'Le mot de passe ne correspond pas.',
            'options' => [
                'row_attr' => [
                    'class' => 'form-group'
                ],
            ],
            'required' => true,
            'first_options'  => [
                'label' => 'Nouveau mot de passe (6 caractères minimum)',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(['message' => 'Le mot de passe ne peut pas être vide']),
                    new Length(['min' => 6, 'minMessage' => 'Votre mot de passe doit faire au moins 6 caractères', 'max' => 10, 'maxMessage' => 'Votre mot de passe doit faire au plus 10 caractères']),
                ],
            ],
            'second_options' => [
                'label' => 'Confirmation du mot de passe',
                'attr' => ['autocomplete' => 'new-password'],
            ],
            'mapped' => false,
        ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
