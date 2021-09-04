<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LicenceNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('licenceNumber', TextType::class, [
                'label' => 'numéro de licence',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Supprimer',
                'attr' => ['class' => 'btn btn-primary float-right'],
            ])
        ;
    }
}
