<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Identity;
use App\Entity\Licence;
use App\Validator\BirthDate;
use App\Validator\Phone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $identity = $event->getData();
            $form = $event->getForm();
            $type = $identity->getType();
            $kinship = 1 < $type;
            $disabled = ($options['season_licence']->isFinal() && Identity::TYPE_MEMBER === $type) ? 'disabled' : '';
            $addressClass = (Identity::TYPE_SECOND_CONTACT !== $type) ? ' identity-address' : '';
            $addressRequired = 'required';
            $row_class = ($kinship) ? 'form-group-inline' : 'form-group';

            if ((!$options['is_kinship'] && !$kinship) || ($options['is_kinship'] && $kinship)) {
                $form
                    ->add('name', TextType::class, [
                        'label' => 'Nom',
                        'row_attr' => [
                            'class' => $row_class,
                        ],
                        'constraints' => [
                            new NotNull(),
                            new NotBlank(),
                        ],
                        'attr' => [
                            'data-constraint' => 'app-UniqueMember',
                        ],
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
                        ],
                        'attr' => [
                            'data-constraint' => 'app-UniqueMember',
                            'data-multiple-fields' => 1,
                        ],
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
                        ],
                    ])
                ;

                if (Identity::TYPE_SECOND_CONTACT !== $type) {
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
                            ],
                        ])
                        ->add('email', EmailType::class, [
                            'label' => 'Adresse mail',
                            'row_attr' => [
                                'class' => 'form-group-inline',
                            ],
                            'constraints' => [
                                new Email(),
                            ],
                            'attr' => [
                                'data-constraint' => 'symfony-Email',
                            ],
                        ])
                        ->add('birthDate', DateTimeType::class, [
                            'label' => 'Date de naissance',
                            'widget' => 'single_text',
                            'html5' => false,
                            'format' => 'dd/MM/yyyy',
                            'attr' => [
                                'class' => 'js-datepicker',
                                'autocomplete' => 'off',
                                'data-constraint' => 'app-BirthDate',
                            ],
                            'row_attr' => [
                                'class' => $row_class,
                            ],
                            'disabled' => $disabled,
                            'constraints' => [new BirthDate()],
                        ])
                        ;
                }

                if (Licence::CATEGORY_ADULT === $options['category'] && $options['season_licence']->isFinal()) {
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
                    ;
                }

                if ($kinship) {
                    $kinshipChoices = Identity::KINSHIPS;
                    if (Identity::TYPE_SECOND_CONTACT !== $type) {
                        unset($kinshipChoices[Identity::KINSHIP_OTHER]);
                        $form
                            ->add('otherAddress', CheckboxType::class, [
                                'label' => 'Réside à une autre adresse que l\'enfant',
                                'required' => false,
                                'mapped' => false,
                                'attr' => [
                                    'class' => 'identity-other-address',
                                ],
                                'data' => ($identity->hasAddress()) ? true : false,
                            ])
                            ;
                    }
                    $form
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
                    if (!$identity->hasAddress()) {
                        $addressClass .= ' hidden';
                        $addressRequired = '';
                    }
                } else {
                    $form
                        ->add('birthplace', TextType::class, [
                            'label' => 'Lieu de naissance',
                            'row_attr' => [
                                'class' => $row_class,
                            ],
                            'constraints' => [
                                new NotNull(),
                                new NotBlank(),
                            ],
                            'attr' => [
                                'data-constraint' => '',
                            ],
                        ])
                        ->add('birthDepartment', ChoiceType::class, [
                            'label' => 'Département de naissance',
                            'placeholder' => 'Sélectinner un département',
                            'choices' => array_flip(json_decode(file_get_contents('../data/departments'), true)),
                            'row_attr' => [
                                'class' => $row_class,
                            ],
                            'constraints' => [
                                new NotNull(),
                                new NotBlank(),
                            ],
                            'attr' => [
                                'data-constraint' => '',
                            ],
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
                        ;
                }
                $form->add('address', AddressType::class, [
                    'row_class' => $addressClass,
                    'required' => $addressRequired,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Identity::class,
            'category' => Licence::CATEGORY_ADULT,
            'season_licence' => null,
            'is_kinship' => false,
        ]);
    }
}
