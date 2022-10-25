<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Commune;
use App\Entity\Identity;
use App\Entity\Licence;
use App\Validator\BirthDate;
use App\Validator\Phone;
use DateInterval;
use DateTime;
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
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

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
            $row_class = ($kinship) ? 'form-group-inline' : 'form-group';


            $addressClass = (Identity::TYPE_MEMBER !== $type) ? ' identity-address' : '';
            $addressRequired = 'required';
            if (!$identity->hasAddress()) {
                $addressRequired = '';
            }

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
                        'attr' => (Identity::TYPE_MEMBER === $type && !$disabled)
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
                        ],
                        'attr' => (Identity::TYPE_MEMBER === $type && !$disabled)
                            ? [
                                'data-constraint' => 'app-UniqueMember',
                                'data-multiple-fields' => 1,
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
                        ],
                    ])
                    ->add('email', EmailType::class, [
                        'label' => (Identity::TYPE_KINSHIP === $type && Licence::CATEGORY_MINOR === $options['category']) ? 'Adresse mail (contact principal)' : 'Adresse mail',
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

                if (Identity::TYPE_SECOND_CONTACT !== $type) {
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
                            ],
                        ])
                        ->add('birthDate', DateTimeType::class, [
                            'label' => 'Date de naissance',
                            'widget' => 'single_text',
                            'html5' => false,
                            'format' => 'dd/MM/yyyy',
                            'attr' => [
                                'class' => 'js-datepicker',
                                'data-max-date' => $dateMax->format('Y-m-d'),
                                'data-min-date' => $dateMin->format('Y-m-d'),
                                'data-year-range' => $dateMin->format('Y') . ':' . $dateMax->format('Y'),
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
                    }
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
                        ->add('birthCommune', Select2EntityType::class, [
                            'label' => 'Lieu de naissance<br><small>(Pour l\'étranger, saisissez la ville et le pays)</small>',
                            'label_html' => true,
                            'class' => Commune::class,
                            'multiple' => false,
                            'remote_route' => 'geo_department',
                            'primary_key' => 'id',
                            'text_property' => 'name',
                            'minimum_input_length' => 1,
                            'page_limit' => 10,
                            'allow_clear' => true,
                            'allow_add' => [
                                'enabled' => true,
                                'new_tag_text' => ' (Hors France)',
                                'tag_separators' => '[";"]',
                            ],
                            'delay' => 250,
                            'cache' => false,
                            'language' => 'fr',
                            'placeholder' => 'Rechercher une commune (sans espace)',
                            'width' => '100%',
                            'row_attr' => [
                                'class' => 'form-group search',
                            ],
                            'attr' => [
                                'class' => 'commune-search',
                                'data-constraint' => 'app-NotEmpty',
                            ],
                            'required' => true,
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
