<?php

declare(strict_types=1);

namespace App\Form\Admin;

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
        $disabled = count($options['licences']) > 1 || !$options['is_yearly'];

        $builder
            ->add('licenceNumber', TextType::class, [
                'label' => 'Numéro de licence',
                'disabled' => $disabled,
                'constraints' => [
                    new Length([
                        'max' => 25,
                    ]), ],
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
            'licences' => [],
            'is_yearly' => false,
        ]);
    }
}
