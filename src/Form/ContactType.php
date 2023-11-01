<?php

declare(strict_types=1);

namespace App\Form;

use App\Validator\EmailHost;
use App\Validator\FullName;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaV3Type;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrueV3;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
}
