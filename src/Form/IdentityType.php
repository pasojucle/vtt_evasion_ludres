<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Enum\IdentityKindEnum;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Entity\User;
use App\Form\Admin\CommuneAutocompleteField;
use App\Validator\BirthDate;
use App\Validator\Phone;
use App\Validator\SchoolTestingRegistration;
use App\Validator\UniqueMember;
use DateInterval;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class IdentityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /**@var Identity $identity */
            $identity = $event->getData();
            $form = $event->getForm();
            $kind = $identity->getKind();
            $kinship = $kind !== IdentityKindEnum::MEMBER;
            $disabled = $this->haspreviousLicence($identity->getUser()) && !$identity->getKinship();
            $row_class = ($kinship) ? 'form-group-inline' : 'form-group';
            $foreignBorn = !$identity->getBirthCommune()?->getPostalCode() && $identity->getId();
            list($birthCommuneClass, $birthPlaceClass) = $this->getBirthPlaceClasses($foreignBorn);
            $isTesting = (!$kinship) ? (int) empty($identity->getUser()->getLicenceNumber()) : 0;
            $addressClass = ($kinship) ? ' identity-address' : '';
            $addressRequired = ($kinship && !$identity->hasAddress()) ? '' : 'required';

            if ($options['is_kinship'] === $kinship) {
                $form
                    ->add('name', TextType::class, [
                        'label' => 'Nom',
                        'row_attr' => [
                            'class' => $row_class,
                        ],
                        'constraints' => [
                            new NotNull(),
                            new NotBlank(),
                            new UniqueMember()
                        ],
                        'attr' => (IdentityKindEnum::MEMBER === $kind && !$disabled)
                            ? [
                                'data-constraint' => 'app-UniqueMember',
                            ]
                            : ['data-constraint' => ''],
                        'disabled' => $disabled,
                    ])
                    ->add('firstName', TextType::class, [
                        'label' => 'Prénom',
                        'row_attr' => [
                            'class' => $row_class,
                        ],
                        'constraints' => [
                            new NotNull(),
                            new NotBlank(),
                            new UniqueMember()
                        ],
                        'attr' => (IdentityKindEnum::MEMBER === $kind && !$disabled)
                            ? [
                                'data-constraint' => 'app-UniqueMember',
                                'data-multiple-fields' => 1,
                                'data-alert-route' => 'unique_member',
                                'autocomplete' => 'off',
                            ]
                            : ['data-constraint' => '', 'autocomplete' => 'off', ],
                        'disabled' => $disabled,
                    ])
                    ->add('mobile', TextType::class, [
                        'label' => 'Téléphone mobile',
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                        'constraints' => [
                            new Phone(),
                        ],
                        'attr' => [
                            'data-constraint' => 'app-Phone',
                            'autocomplete' => 'off',
                            'class' => 'phone-number',
                        ],
                    ])
                    ->add('email', EmailType::class, [
                        'label' => (IdentityKindEnum::KINSHIP === $kind && Licence::CATEGORY_MINOR === $options['category']) ? 'Adresse mail (contact principal)' : 'Adresse mail',
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                        'constraints' => [
                            new Email(),
                        ],
                        'attr' => [
                            'data-constraint' => 'symfony-Email',
                            'autocomplete' => 'off',
                        ],
                    ])
                    ->add('address', AddressType::class, [
                        'row_class' => $addressClass,
                        'required' => $addressRequired,
                    ])
                ;

                if (IdentityKindEnum::SECOND_CONTACT !== $kind) {
                    $dateMax = (new DateTime())->sub(new DateInterval('P5Y'));
                    $dateMin = (new DateTime())->sub(new DateInterval('P80Y'));
                    $form
                        ->add('phone', TextType::class, [
                            'label' => 'Téléphone fixe',
                            'required' => false,
                            'row_attr' => [
                                'class' => 'form-group-inline',
                            ],
                            'constraints' => [
                                new Phone(),
                            ],
                            'attr' => [
                                'data-constraint' => 'app-Phone',
                                'autocomplete' => 'off',
                                'class' => 'phone-number',
                            ],
                        ])
                        ->add('birthDate', DateType::class, [
                            'label' => 'Date de naissance',
                            // 'widget' => 'single_text',
                            // 'html5' => false,
                            // 'format' => 'dd/MM/yyyy',
                            'attr' => [
                                // 'class' => 'js-datepicker',
                                'nin' => $dateMin->format('Y-m-d'),
                                'max' => $dateMax->format('Y-m-d'),
                                'data-max-date' => $dateMax->format('Y-m-d'),
                                'data-min-date' => $dateMin->format('Y-m-d'),
                                'data-year-range' => $dateMin->format('Y') . ':' . $dateMax->format('Y'),
                                'autocomplete' => 'off',
                                'data-constraint' => 'app-BirthDate;app-SchoolTestingRegistration',
                                'data-extra-param-name' => 'isTesting',
                                'data-extra-value' => $isTesting,
                                'data-alert-route' => 'registration_scholl_testing_disabled',
                            ],
                            'row_attr' => [
                                'class' => $row_class,
                            ],
                            'disabled' => $disabled,
                            'constraints' => [
                                new BirthDate(),
                            ],
                        ])
                        ;
                }

                if (Licence::CATEGORY_ADULT === $options['category'] && $options['is_final']) {
                    $form
                        ->add('profession', TextType::class, [
                            'label' => 'Profession',
                            'row_attr' => [
                                'class' => 'form-group-inline',
                            ],
                            'attr' => [
                                'data-constraint' => '',
                            ],
                            'required' => false,
                        ])
                        ->add('emergencyPhone', TextType::class, [
                            'label' => 'Télephone de la personne à prévenir en cas d\'urgence',
                            'row_attr' => [
                                'class' => 'form-group-inline full-width',
                            ],
                            'constraints' => [
                                new Phone(),
                            ],
                            'attr' => [
                                'data-constraint' => 'app-Phone',
                                'data-multiple-fields' => 1,
                                'autocomplete' => 'off',
                                'class' => 'phone-number',
                            ],
                        ])
                    ;
                }

                if ($kinship) {
                    $kinshipChoices = Identity::KINSHIPS;
                    if (IdentityKindEnum::SECOND_CONTACT !== $kind) {
                        unset($kinshipChoices[Identity::KINSHIP_OTHER]);
                    }
                    $form
                        ->add('otherAddress', CheckboxType::class, [
                            'label' => 'Réside à une autre adresse que l\'enfant',
                            'required' => false,
                            'mapped' => false,
                            'attr' => [
                                'class' => 'identity-other-address',
                                'data-modifier' => sprintf('address-container-%s', $form->getName())
                            ],
                            'data' => ($identity->hasAddress()) ? true : false,
                        ])
                        ->add('kinship', ChoiceType::class, [
                            'label' => 'Parenté',
                            'choices' => array_flip($kinshipChoices),
                            'placeholder' => 'Choisir le lien de parenté',
                            'row_attr' => [
                                'class' => 'form-group-inline',
                            ],
                            'attr' => [
                                'data-constraint' => '',
                            ],
                        ])
                    ;
                } else {
                    $form
                        ->add('birthCommune', CommuneAutocompleteField::class, [
                            'label' => 'Lieu de naissance',
                            'row_attr' => [
                                'class' => $birthCommuneClass,
                            ],
                            'attr' => [
                                'data-constraint' => '',
                            ],
                            'required' => !$foreignBorn,
                        ])
                        ->add('birthPlace', TextType::class, [
                            'label' => 'Lieu de naissance',
                            'row_attr' => [
                                'class' => $birthPlaceClass,
                            ],
                            'attr' => [
                                'data-constraint' => '',
                            ],
                            'required' => $foreignBorn,
                        ])
                        ->add('birthCountry', TextType::class, [
                            'label' => 'Pays de naissance',
                            'row_attr' => [
                                'class' => $birthPlaceClass,
                            ],
                            'attr' => [
                                'data-constraint' => '',
                            ],
                            'required' => $foreignBorn,
                        ])
                        ->add('foreignBorn', CheckboxType::class, [
                            'label' => 'Je suis né à l\'étranger',
                            'mapped' => false,
                            'required' => false,
                            'row_attr' => [
                                'class' => 'form-group-inline',
                            ],
                            'attr' => [
                                'class' => 'foreign-born',
                            ],
                            'data' => $foreignBorn,
                        ])
                        ->add('pictureFile', FileType::class, [
                            'label' => 'Photo d\'itentité',
                            'mapped' => false,
                            'required' => false,
                            'block_prefix' => 'custom_file',
                            'attr' => [
                                'accept' => '.bmp,.jpeg,.jpg,.png',
                            ],
                            'constraints' => [
                                new File([
                                    'maxSize' => '1024k',
                                    'mimeTypes' => [
                                        'image/bmp',
                                        'image/jpeg',
                                        'image/png',
                                    ],
                                    'mimeTypesMessage' => 'Format image bmp, jpeg ou png autorisé',
                                ]),
                            ],
                        ])
                        ->add('schoolTestingRegistration', HiddenType::class, [
                            'mapped' => false,
                            'constraints' => [
                                new SchoolTestingRegistration(),
                            ],
                        ])
                        ;
                    ;
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Identity::class,
            'category' => Licence::CATEGORY_ADULT,
            'is_final' => false,
            'is_kinship' => false,
        ]);
    }

    private function haspreviousLicence(?User $user): bool
    {
        return true === $user->getLastLicence()?->isFinal();
    }

    private function getBirthPlaceClasses(bool $foreignBorn): array
    {
        $birthPlaceClasses = ['form-group-inline birth-place', 'form-group-inline birth-place d-none'];
        if ($foreignBorn) {
            return array_reverse($birthPlaceClasses);
        };

        return $birthPlaceClasses;
    }
}
