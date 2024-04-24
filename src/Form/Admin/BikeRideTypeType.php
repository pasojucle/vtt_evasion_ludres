<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\BikeRideType;
use App\Entity\Message;
use App\Validator\NotEmptyArray;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class BikeRideTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'empty_data' => 'Nouveau type de sortie',
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu',
                'config_name' => 'minimum_config',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('needFramers', CheckboxType::class, [
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-switch-on' => 'Nécessites un encadrement',
                    'data-switch-off' => 'Sans encadrement',
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
            ->add('registration', ChoiceType::class, [
                'label' => 'Inscriptions',
                'choices' => array_flip(BikeRideType::REGISTRATIONS),
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'class' => 'form-modifier',
                    'data-modifier' => 'bikeRideTypeContainer',
                ],
            ])
            ->add('messages', Select2EntityType::class, [
                'class' => Message::class,
                'remote_route' => 'admin_message_autocomplete',
                'primary_key' => 'id',
                'text_property' => 'label',
                'multiple' => true,
                'minimum_input_length' => 0,
                'page_limit' => 10,
                'allow_clear' => true,
                'delay' => 250,
                'cache' => true,
                'cache_timeout' => 60000,
                'language' => 'fr',
                'placeholder' => 'Selectionner un message',
                'width' => '100%',
                'required' => true,
            ])
            ->add('useLevels', CheckboxType::class, [
                'label_html' => true,
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-switch-on' => 'Afficher le niveau des participants',
                    'data-switch-off' => 'Ne pas afficher le niveau des participants',
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
                    'data-switch-on' => 'Afficher la liste des participants à l\'inscription',
                    'data-switch-off' => 'Ne pas afficher la liste des participants à l\'inscription',
                ],
            ])
            ->add('displayBikeKind', CheckboxType::class, [
                'label_html' => true,
                'block_prefix' => 'switch',
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-switch-on' => 'Afficher le type de vélo à l\'inscription',
                    'data-switch-off' => 'Ne pas afficher le type de vélo à l\'inscription',
                ],
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
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
            ;
        $formModifier = function (FormInterface $form, ?int $registration) {
            if (BikeRideType::REGISTRATION_CLUSTERS === $registration) {
                $form->add('clusters', CollectionType::class, [
                    'label' => 'Groupes',
                    'entry_options' => [
                        'label' => false,
                        'row_attr' => [
                            'class' => 'row form-group-collection',
                        ],
                        'attr' => [
                            'class' => 'col-md-11',
                        ],
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'constraints' => [
                        new NotEmptyArray(),
                    ],
                    'error_bubbling' => false,
                ]);
            } else {
                $form->remove('clusters');
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $form = $event->getForm();
            $data = $event->getData();

            $formModifier($form, $data?->getRegistration());
        });

        $builder->get('registration')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $registration = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $registration);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BikeRideType::class,
        ]);
    }
}
