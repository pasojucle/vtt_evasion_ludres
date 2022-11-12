<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\DiseaseKind;
use App\Entity\Licence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiseaseKindType extends AbstractType
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
            ->add('category', ChoiceType::class, [
                'label' => 'Famille',
                'choices' => array_flip(DiseaseKind::CATEGORIES),
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('licenceCategory', ChoiceType::class, [
                'label' => 'Catégory',
                'placeholder' => 'licence.category.place_holder',
                'choices' => array_flip(Licence::CATEGORIES),
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('bikeRideAlert', CheckboxType::class, [
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-switch-on' => 'Signaler d\'une croix rouge dans la liste des adhérents d\'une sortie',
                    'data-switch-off' => 'Ne pas signaler dans la liste des adhérents d\'une sortie',
                ],
            ])
            ->add('customLabel', CheckboxType::class, [
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-switch-on' => 'Besoin de préciser la pathologie',
                    'data-switch-off' => 'La patologie est dans le nom',
                ],
            ])
            ->add('emergencyTreatment', CheckboxType::class, [
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-switch-on' => 'Besoin de préciser le traitement d\'urgence',
                    'data-switch-off' => 'Pas de traitement d\'urgence',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DiseaseKind::class,
        ]);
    }
}
