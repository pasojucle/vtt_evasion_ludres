<?php

declare(strict_types=1);

namespace App\Form;

use App\Validator\EmailHost;
use App\Validator\FullName;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaV3Type;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrueV3;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new FullName(),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse mail',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new EmailHost(),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('recaptcha', EWZRecaptchaV3Type::class, [
                'label' => false,
                'mapped' => false,
                'constraints' => [
                    new IsTrueV3(),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
