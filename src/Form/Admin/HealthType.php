<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Health;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HealthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('medicalCertificateDate', DateTimeType::class, [
                'label' => 'Date du dernier certificat médical',
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
                'required' => false,
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Pathologie',
                'attr' => [
                    'class' => 'textarea',
                ],
                'row_attr' => [
                    'class' => 'form-group',
                ],
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Modifier',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Health::class,
        ]);
    }
}
