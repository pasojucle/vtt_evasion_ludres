<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Health;
use App\Validator\Phone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class HealthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null !== $options['current'] && UserType::FORM_HEALTH === $options['current']->getForm() || null === $options['current']) {
            $builder
                ->add('socialSecurityNumber', TextType::class, [
                    'label' => 'Numéro de sécurité sociale',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'constraints' => [
                        new NotNull(),
                        new NotBlank(),
                    ],
                    'attr' => [
                        'data-constraint' => '',
                    ],
                ])
                ->add('mutualCompany', TextType::class, [
                    'label' => 'Mutuelle',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'constraints' => [
                        new NotNull(),
                        new NotBlank(),
                    ],
                    'attr' => [
                        'data-constraint' => '',
                    ],
                ])
                ->add('mutualNumber', TextType::class, [
                    'label' => 'Numéro',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'constraints' => [
                        new NotNull(),
                        new NotBlank(),
                    ],
                    'attr' => [
                        'data-constraint' => '',
                    ],
                ])
                ->add('bloodGroup', TextType::class, [
                    'label' => 'Groupe sanguin',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'required' => false,
                    'attr' => [
                        'data-constraint' => '',
                    ],
                ])
                ->add('tetanusBooster', DateType::class, [
                    'label' => 'Date du dernier rappel antitétanique',
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    'attr' => [
                        'class' => 'js-datepicker',
                        'autocomplete' => 'off',
                        'data-constraint' => '',
                    ],
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'constraints' => [
                        new NotNull(),
                        new NotBlank(),
                    ],
                ])
                ->add('doctorName', TextType::class, [
                    'label' => 'Nom du médecin traitant',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'attr' => [
                        'data-constraint' => '',
                    ],
                ])
                ->add('doctorAddress', TextType::class, [
                    'label' => 'Adresse du medecin traitant',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'constraints' => [
                        new NotNull(),
                        new NotBlank(),
                    ],
                    'attr' => [
                        'data-constraint' => '',
                    ],
                ])
                ->add('doctorTown', TextType::class, [
                    'label' => 'Ville du medecin traitant',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'constraints' => [
                        new NotNull(),
                        new NotBlank(),
                    ],
                    'attr' => [
                        'data-constraint' => '',
                    ],
                ])
                ->add('doctorPhone', TextType::class, [
                    'label' => 'Télephone du medecin traitant',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'constraints' => [
                        new Phone(),
                    ],
                    'attr' => [
                        'data-constraint' => 'app-Phone',
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
                ])
            ;
        }

        if (null === $options['current']) {
            $builder
                ->add('save', SubmitType::class, [
                    'label' => 'Modifier',
                    'attr' => [
                        'class' => 'btn btn-primary float-right',
                    ],
                ])
            ;
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
