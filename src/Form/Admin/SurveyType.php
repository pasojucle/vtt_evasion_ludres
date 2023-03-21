<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\BikeRide;
use App\Entity\Survey;
use App\Entity\User;
use App\Form\Transformer\BikeRideTransformer;
use App\Validator\CKEditorBlank;
use Doctrine\Common\Collections\Collection;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class SurveyType extends AbstractType
{
    public const DISPLAY_ALL_MEMBERS = null;
    public const DISPLAY_BIKE_RIDE = 1;
    public const DISPLAY_MEMBER_LIST = 2;

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
                // 'required' => false,
                'constraints' => [
                    new CKEditorBlank(),
                ],
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
            ->add('displayCriteria', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Afficher le sondage à tous les adhérents' => self::DISPLAY_ALL_MEMBERS,
                    'Afficher le sondage à l\'inscription à une sortie' => self::DISPLAY_BIKE_RIDE,
                    'Afficher le sondage à une liste d\'adhérents' => self::DISPLAY_MEMBER_LIST,
                ],
                'choice_attr' => function () {
                    return [
                        'data-modifier' => 'surveyDisplay',
                        'class' => 'form-modifier',
                     ];
                },
                'disabled' => $options['display_disabled'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;

        $formModifier = function (FormInterface $form, array $options, ?int $displayCriteria, ?BikeRide $bikeRide, ?Collection $members) {
            $disabled = (null !== $displayCriteria);
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
                    'disabled' => $options['display_disabled'],
                ])
                ->add('isAnonymous', CheckboxType::class, [
                    'block_prefix' => 'switch',
                    'attr' => [
                        'data-switch-on' => 'Mode anonyme activé',
                        'data-switch-off' => 'Activer le mode anonyme',
                    ],
                    'required' => false,
                    'disabled' => $options['display_disabled'],
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
                    'label' => false,
                    'required' => false,
                    'disabled' => (!$options['display_disabled']) ? self::DISPLAY_BIKE_RIDE !== $displayCriteria : $disabled,
                    'data' => (self::DISPLAY_BIKE_RIDE === $displayCriteria) ? $bikeRide : null,
                ])
                ->add('members', Select2EntityType::class, [
                    'multiple' => true,
                    'remote_route' => 'admin_member_choices',
                    'class' => User::class,
                    'primary_key' => 'id',
                    'text_property' => 'fullName',
                    'minimum_input_length' => 0,
                    'page_limit' => 10,
                    'allow_clear' => true,
                    'delay' => 250,
                    'cache' => true,
                    'cache_timeout' => 60000,
                    // if 'cache' is true
                    'language' => 'fr',
                    'placeholder' => 'Sélectionnez les adhérents',
                    'width' => '100%',
                    'label' => false,
                    'required' => false,
                    'disabled' => (!$options['display_disabled']) ? self::DISPLAY_MEMBER_LIST !== $displayCriteria : $disabled,
                    'remote_params' => [
                        'filters' => json_encode($options['filters']),
                    ],
                    'data' => (self::DISPLAY_MEMBER_LIST === $displayCriteria) ? $members : null,
                ])
                ;
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options, $formModifier) {
            $form = $event->getForm();
            $data = $event->getData();

            $formModifier($form, $options, $data->getDisplayCriteria(), $data->getBikeRide(), $data->getMembers());
        });

        $builder->get('displayCriteria')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($options, $formModifier) {
                $displayCriteria = $event->getForm()->getData();
                $survey = $event->getForm()->getParent()->getData();
                $formModifier($event->getForm()->getParent(), $options, $displayCriteria, $survey->getBikeRide(), $survey->getMembers());
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Survey::class,
            'filters' => [],
            'display_disabled' => false,
        ]);
    }
}
