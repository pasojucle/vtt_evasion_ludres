<?php

namespace App\Form;

use App\Entity\Link;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

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
            'csrf_token_id'   => 'authenticate',
        ]);
    }
}
