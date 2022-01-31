<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('licenceNumber', TextType::class, [
                'label' => 'Numéro de licence',
                'attr' => [
                    'autocomplete' => 'userName',
                    'autofocus' => true,
                ],
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('_rememberMe', CheckboxType::class, [
                'label' => 'Rester connecté',
                'data' => true,
                'required' => false,
                'block_prefix' => 'customsimplecheck',
                // 'row_attr' => [
                //     'class' => 'form-group',
                // ],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'authenticate',
        ]);
    }
}
