<?php

namespace App\Form\Admin;

use App\Entity\Agreement;
use App\Entity\Enum\AgreementKindEnum;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceMembershipEnum;
use App\Entity\RegistrationStep;
use App\Form\Type\CkeditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

class AgreementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', TextType::class, [
                'label' => 'Nom (en Upper camel case)',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Unique()
                ]
            ])
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('content', CkeditorType::class, [
                'label' => 'Contenu',
                'config_name' => 'base',
                'row_attr' => [
                    'class' => 'form-group',
                ],
            ])
            ->add('category', EnumType::class, [
                'label' => 'Catégorie',
                'class' => LicenceCategoryEnum::class,
                'autocomplete' => true,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('membership', EnumType::class, [
                'label' => 'Adhésion',
                'class' => LicenceMembershipEnum::class,
                'autocomplete' => true,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
            ])
            ->add('kind', EnumType::class, [
                'label' => 'Type',
                'class' => AgreementKindEnum::class,
                'autocomplete' => true,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'class' => 'form-modifier',
                    'data-modifier' => 'autorization-container'
                ],
            ])
            ->add('registrationSteps', EntityType::class, [
                'label' => 'Étape d\'inscription',
                'class' => RegistrationStep::class,
                'choice_label' => 'title',
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-group row',
                ],
                'block_prefix' => 'collection_check',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                    'label' => 'Enregister',
                    'attr' => [
                        'class' => 'btn btn-primary float-right',
                    ],
                ])
        ;
        $formModifier = function (FormInterface $form, AgreementKindEnum $agreement) {
            if (AgreementKindEnum::AUTHORIZATION === $agreement) {
                $form->add('authorizationMessage', TextType::class, [
                    'label' => 'Message d\'autorisation',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                ])
                ->add('rejectionMessage', TextType::class, [
                    'label' => 'Message de refus',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                ])
                ->add('authorizationIcon', TextType::class, [
                    'label' => 'Icône d\'autorisation',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                ])
                ->add('rejectionIcon', TextType::class, [
                    'label' => 'Icône de refus',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                ]);
            } else {
                $form
                    ->remove('authorizationMessage')
                    ->remove('rejectionMessage')
                    ->remove('authorizationIcon')
                    ->remove('rejectionIcon');
            }
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            /** @var Agreement $agreement */
            $agreement = $event->getData();
            $formModifier($event->getForm(), $agreement->getKind());
        });

        $builder->get('kind')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $kind = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $kind);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Agreement::class,
        ]);
    }
}
