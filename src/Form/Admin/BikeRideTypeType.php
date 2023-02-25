<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\BikeRideType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu',
                'config_name' => 'minimum_config',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('isRegistrable', CheckboxType::class, [
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-switch-on' => 'Ouvert aux inscriptions',
                    'data-switch-off' => 'Fermé aux inscriptions',
                ],
            ])
            ->add('isCompensable', CheckboxType::class, [
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-switch-on' => 'Indemnités pour les encadrants',
                    'data-switch-off' => 'Aucune d\'indemnité',
                ],
            ])
            ->add('isSchool', CheckboxType::class, [
                'label_html' => true,
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-switch-on' => 'Inscription par groupes de niveaux de l\'école VTT',
                    'data-switch-off' => 'Un seul groupe',
                ],
            ])
            ->add('useLevels', CheckboxType::class, [
                'label_html' => true,
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-switch-on' => 'Afficher le niveau des inscrits',
                    'data-switch-off' => 'ne pas afficher le niveau des inscrits',
                ],
            ])
            ->add('showMemberList', CheckboxType::class, [
                'label_html' => true,
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-switch-on' => 'Afficher la liste des participants à l\'inscrition',
                    'data-switch-off' => 'ne pas afficher la liste des participants à l\'inscrition',
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
            'data_class' => BikeRideType::class,
        ]);
    }
}
