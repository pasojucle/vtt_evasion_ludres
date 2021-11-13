<?php

namespace App\Form;

use App\Entity\Health;
use App\Form\DiseaseType;
use App\Form\HealthQuestionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class HealthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null !== $options['current'] && UserType::FORM_HEALTH === $options['current']->getForm() || null === $options['current']) {
            $builder
                ->add('socialSecurityNumber', TextType::class, [
                    'label' => 'Numéro de sécurité sociale',
                    'row_attr' => [
                        'class' => 'form-group-inline'
                    ]
                ])
                ->add('mutualCompany', TextType::class, [
                    'label' => 'Mutuelle',
                    'row_attr' => [
                        'class' => 'form-group-inline'
                    ]
                ])
                ->add('mutualNumber', TextType::class, [
                    'label' => 'Numéro',
                    'row_attr' => [
                        'class' => 'form-group-inline'
                    ]
                ])
                ->add('bloodGroup', TextType::class, [
                    'label' => 'Groupe sanguin',
                    'row_attr' => [
                        'class' => 'form-group-inline'
                    ],
                    'required' => false,
                ])
                ->add('tetanusBooster', DateType::class, [
                    'label' => 'Date du dernier rappel antitétanique',
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    'attr' => [
                        'class' => 'js-datepicker',
                        'autocomplete' => "off",
                    ],
                    'row_attr' => [
                        'class' => 'form-group-inline'
                    ]
                ])
                ->add('doctorName', TextType::class, [
                    'label' => 'Nom du médecin traitant',
                    'row_attr' => [
                        'class' => 'form-group-inline'
                    ]
                ])
                ->add('doctorAddress', TextType::class, [
                    'label' => 'Adresse du medecin traitant',
                    'row_attr' => [
                        'class' => 'form-group-inline'
                    ]
                ])
                ->add('doctorTown', TextType::class, [
                    'label' => 'Ville du medecin traitant',
                    'row_attr' => [
                        'class' => 'form-group-inline'
                    ]
                ])
                ->add('doctorPhone', TextType::class, [
                    'label' => 'Télephone du medecin traitant',
                    'row_attr' => [
                        'class' => 'form-group-inline'
                    ],
                    'constraints' => [
                        new Length(['min' => 10, 'max' => 10]),
                    ],
                ])
                ;


            $builder
                ->add('diseases', CollectionType::class, [
                    'label' => false,
                    'entry_type' => DiseaseType::class,
                    'entry_options' => [
                        'label' => false,
                        'current' => $options['current'],
                    ],
                ])
            ;
        }
        if (null !== $options['current'] && UserType::FORM_HEALTH_QUESTION === $options['current']->getForm()) {
            $builder
                ->add('healthQuestions', CollectionType::class, [
                    'label' => false,
                    'entry_type' => HealthQuestionType::class,
                ]);
            ;
        }

        if (null == $options['current']) {
            $builder
                ->add('save', SubmitType::class, [
                    'label' => 'Modifier',
                    'attr' => ['class' => 'btn btn-primary float-right'],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Health::class,
            'current' => null,
        ]);
    }
}
