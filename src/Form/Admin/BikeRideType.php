<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\BikeRide;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BikeRideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Type de randonnée',
                'choices' => array_flip(BikeRide::TYPES),
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Détail (optionnel)',
                'config_name' => 'minimum_config',
                'required' => false,
            ])
            ->add('startAt', DateTimeType::class, [
                'label' => 'Date de départ',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => [
                    'class' => 'js-datepicker',
                    'autocomplete' => 'off',
                ],
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
        ;

        $formModifier = function (FormInterface $form, int $type) {
            $isDiabled = (BikeRide::TYPE_HOLIDAYS === $type) ? 'disabled' : '';

            $form
                ->add('endAt', DateTimeType::class, [
                    'label' => 'Date de fin (optionnel)',
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    'attr' => [
                        'class' => 'js-datepicker',
                        'autocomplete' => 'off',
                    ],
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'required' => false,
                    'disabled' => $isDiabled,
                ])
                ->add('displayDuration', IntegerType::class, [
                    'label' => 'Durée d\'affichage (nbr de jours avant)',
                    'required' => false,
                    'attr' => [
                        'min' => 0,
                        'max' => 90,
                    ],
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'disabled' => $isDiabled,
                ])
                ->add('closingDuration', IntegerType::class, [
                    'label' => 'Fin d\'inscription (nbr de jours avant)',
                    'required' => false,
                    'attr' => [
                        'min' => 0,
                        'max' => 90,
                    ],
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'disabled' => $isDiabled,
                ])
                ->add('minAge', IntegerType::class, [
                    'label' => 'Age minimum (optionnel)',
                    'attr' => [
                        'min' => 10,
                        'max' => 90,
                    ],
                    'required' => false,
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'disabled' => $isDiabled,
                ])
                ->add('save', SubmitType::class, [
                    'label' => 'Enregister',
                    'attr' => [
                        'class' => 'btn btn-primary float-right',
                    ],
                ])
            ;
        };
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $bikeRide) use ($formModifier) {
            $data = $bikeRide->getData();
            $formModifier($bikeRide->getForm(), $data->getType());
        });

        $builder->get('type')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $type = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $type);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BikeRide::class,
        ]);
    }
}
