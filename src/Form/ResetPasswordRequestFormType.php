<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ResetPasswordRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('licenceNumber', TextType::class, [
                'label' => 'Numéro de licence',
                'attr' => ['autocomplete' => 'licenceNumber'],
                'row_attr' => [
                    'class' => 'form-group-inline'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Saisir votre numéro de licence',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
