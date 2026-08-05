<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Health;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HealthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('medicalCertificateDate', DateType::class, [
                'label' => 'Date du dernier certificat médical',
                'attr' => [
                    'autocomplete' => 'off',
                ],
                'required' => false,
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Pathologie',
                'required' => false,
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
