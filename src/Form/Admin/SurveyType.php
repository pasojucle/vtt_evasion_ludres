<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Survey;
use App\Entity\BikeRide;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Form\Transformer\BikeRideTransformer;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\VarDumper\Cloner\Data;

class SurveyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu',
                'config_name' => 'minimum_config',
                'required' => false,
            ])
            ->add('startAt', DateTimeType::class, [
                'label' => 'Date de début',
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
            ->add('endAt', DateTimeType::class, [
                'label' => 'Date de fin',
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
            
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();
            $disabled = !$data->getRespondents()->isEmpty();             

            $form
                ->add('surveyIssues', CollectionType::class, [
                    'label' => false,
                    'entry_type' => SurveyIssueType::class,
                    'entry_options' => [
                        'label' => false,
                        'attr' => [
                            'class' => 'row form-group-collection',
                        ],
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'disabled' => $disabled,
                ])
                ->add('isAnonymous', CheckboxType::class, [
                    'block_prefix' => 'switch',
                    'attr' => [
                        'data-switch-on' => 'Mode anonyme activé',
                        'data-switch-off' => 'Activer le mode anonyme',
                    ],
                    'required' => false,
                    'disabled' => $disabled,
                ])
                ->add('bikeRide', Select2EntityType::class, [
                    'multiple' => false,
                    'remote_route' => 'admin_bike_ride_choices',
                    'class' => BikeRide::class,
                    'primary_key' => 'id',
                    'transformer' => BikeRideTransformer::class,
                    'minimum_input_length' => 0,
                    'page_limit' => 10,
                    'allow_clear' => true,
                    'delay' => 250,
                    'cache' => true,
                    'cache_timeout' => 60000,
                    // if 'cache' is true
                    'language' => 'fr',
                    'placeholder' => 'Sélectionnez une sortie',
                    'width' => '100%',
                    'label' => 'Afficher le sondage à l\'inscription à une sortie (optionnel)',
                    'required' => false,
                    'disabled' => $disabled,
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Survey::class,
        ]);
    }
}
