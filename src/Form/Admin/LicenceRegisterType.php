<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Dto\Payload\LicenceRegister;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class LicenceRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('licenceNumber', TextType::class, [
                'label' => 'Numéro de licence',
                'disabled' => $options['disabled_licence_number'],
                'constraints' => [
                    new Length([
                        'max' => 25,
                    ]),
                ],
                'attr' => [
                    'maxlength' => 25,
                ],
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('medicalCertificateDate', DateTimeType::class, [
                'label' => 'Date du certificat médical',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'autocomplete' => 'off',
                ],
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LicenceRegister::class,
            'disabled_licence_number' => false,
        ]);
    }
}
