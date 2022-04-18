<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\BikeRideType as EntityBikeRideType;
use App\Entity\BikeRide;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class BikeRideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bikeRideType', EntityType::class, [
                'label' => 'Type de randonnée',
                'class' => EntityBikeRideType::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('brt')
                        ->orderBy('brt.name', 'ASC')
                    ;
                },
                'choice_label' => 'name',
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
                'input' => 'datetime_immutable',
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

        $formModifier = function (FormInterface $form, ?EntityBikeRideType $bikeRideType) {
            $isDiabled = (!$bikeRideType?->isRegistrable()) ? 'disabled' : '';

            $form
                ->add('endAt', DateTimeType::class, [
                    'input' => 'datetime_immutable',
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
            $formModifier($bikeRide->getForm(), $data->getBikeRideType());
        });

        $builder->get('bikeRideType')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $bikeRideType = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $bikeRideType);
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
