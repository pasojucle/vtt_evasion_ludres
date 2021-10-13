<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class LicenceValidateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $disabled = count($options['licences']) > 1 || !$options['is_final'];

        $builder
            ->add('licenceNumber', TextType::class, [
                'label' => 'Numéro de licence',
                'disabled' => $disabled,
                'constraints' => [new Length(['max' => 25])],
                'attr' => [
                    'maxlength' => 25,
                ],
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ;

        if ($options['is_final']) {
            $builder
                ->add('medicalCertificateDate', DateTimeType::class, [
                    'label' => 'Date du certificat médical',
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    'attr' => [
                        'class' => 'js-datepicker',
                        'autocomplete' => "off",
                    ],
                    'row_attr' => [
                        'class' => 'form-group',
                    ],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'licences' => [],
            'is_final' => false,
        ]);
    }
}
