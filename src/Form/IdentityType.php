<?php

namespace App\Form;

use App\Entity\Licence;
use App\Entity\Identity;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class IdentityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $identity = $event->getData();
            $form = $event->getForm();
            $type = $identity->getType();
            $kinship = $identity->getKinship();
            $disabled = ($options['season_licence']->isFinal() && $type === Identity::TYPE_MEMBER) ? 'disabled' : '';
            $addressClass = ($type !== Identity::TYPE_SECOND_CONTACT) ? ' identity-address' : '';
            $row_class =  ($kinship) ? 'form-group-inline' : 'form-group';

            if ((!$options['is_kinship'] && !$kinship) || ($options['is_kinship'] && $kinship)) {
                $form
                    ->add('name', TextType::class, [
                        'label' => 'Nom',
                        'row_attr' => [
                            'class' => $row_class
                        ],
                        'disabled' => $disabled,
                    ])
                    ->add('firstName', TextType::class, [
                        'label' => 'Prénom',
                        'row_attr' => [
                            'class' => $row_class,
                        ],
                        'disabled' => $disabled,
                    ])
                    ->add('mobile', TextType::class, [
                        'label' => 'Téléphone mobile',
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                        'constraints' => [
                            new Length(['min' => 10, 'max' => 10]),
                        ],
                    ]);
                    
                    if ($type !== Identity::TYPE_SECOND_CONTACT) {
                        $form
                            ->add('phone', TextType::class, [
                                'label' => 'Téléphone fixe',
                                'required' => false,
                                'row_attr' => [
                                    'class' => 'form-group-inline',
                                ],
                                'constraints' => [
                                    new Length(['min' => 10, 'max' => 10]),
                                ],
                            ])
                            ->add('email', EmailType::class, [
                                'label' => 'Adresse mail',
                                'row_attr' => [
                                    'class' => 'form-group-inline'
                                ],
                            ])
                            ->add('birthDate', DateTimeType::class, [
                                'label' => 'Date de naissance',
                                'widget' => 'single_text',
                                'html5' => false,
                                'format' => 'dd/MM/yyyy',
                                'attr' => [
                                    'class' => 'js-datepicker',
                                    'autocomplete' => "off",
                                ],
                                'row_attr' => [
                                    'class' => $row_class,
                                ],
                                'disabled' => $disabled,
                            ])
                        ;
                    }

                if (Licence::CATEGORY_ADULT === $options['category']) {
                    $form
                    ->add('profession', TextType::class, [
                        'label' => 'Profession',
                        'row_attr' => [
                            'class' => 'form-group-inline'
                        ],
                        'required' => false,
                    ])
                    ;
                }

                if ($kinship) {
                    $kinshipChoices = Identity::KINSHIPS;
                    if ($type !== Identity::TYPE_SECOND_CONTACT) {
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
                        'row_attr' => [
                            'class' => 'form-group-inline'
                        ],
                    ]);
                    if (!$identity->hasAddress()) {
                        $addressClass .=' hidden';
                    }
                } else {
                    $form
                        ->add('birthplace', TextType::class, [
                            'label' => 'Lieu de naissance',
                            'row_attr' => [
                                'class' => $row_class,
                            ],
                        ])
                        ->add('birthDepartment', ChoiceType::class, [
                            'label' => 'Département de naissance',
                            'choices' => array_flip(json_decode(file_get_contents('../data/departments'), true)),
                            'row_attr' => [
                                'class' => $row_class,
                            ],
                        ])
                        ->add('pictureFile', FileType::class, [
                            'label' => 'Photo d\'itentité',
                            'mapped' => false,
                            'required' => false,
                            'block_prefix' => 'custom_file',
                            'attr' => [
                                'accept' => '.bmp,.jpeg,.jpg,.png'
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
                                ])
                            ],
                        ])
                        ;
                }
                $form->add('address', AddressType::class, [
                    'row_class' => $addressClass,
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
