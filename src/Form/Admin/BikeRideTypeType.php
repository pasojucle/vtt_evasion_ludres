<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\BikeRideType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BikeRideTypeType extends AbstractType
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
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('isRegistrable', CheckboxType::class, [
                'label' => 'Possibilté de s\'incrire',
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('isCompensable', CheckboxType::class, [
                'label' => 'Possibilté d\'indemnité pour les encadrants',
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('isSchool', CheckboxType::class, [
                'label' => 'Inscription par groupes de niveaux de l\'école VTT <br>(1 seul groupe par défaut)',
                'label_html' => true,
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregister',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BikeRideType::class,
        ]);
    }
}
