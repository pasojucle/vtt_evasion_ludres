<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Licence;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public const FORM_REGISTRATION_DOCUMENT = 0;

    public const FORM_HEALTH_QUESTION = 1;

    public const FORM_IDENTITY = 2;

    public const FORM_HEALTH = 3;

    public const FORM_APPROVAL = 4;

    public const FORM_LICENCE_COVERAGE = 5;

    public const FORM_MEMBERSHIP_FEE = 6;

    public const FORM_REGISTRATION_FILE = 7;

    public const FORM_LICENCE_TYPE = 8;

    public const FORM_OVERVIEW = 9;

    public const FORM_MEMBER = 10;

    public const FORM_KINSHIP = 11;

    public const FORMS = [
        self::FORM_REGISTRATION_DOCUMENT => 'form.registration_document',
        self::FORM_HEALTH_QUESTION => 'form.health_question',
        self::FORM_IDENTITY => 'form.identity',
        self::FORM_HEALTH => 'form.health',
        self::FORM_APPROVAL => 'form.approval_right_image',
        self::FORM_LICENCE_COVERAGE => 'form.licence_coverage',
        self::FORM_MEMBERSHIP_FEE => 'form.membership_fee',
        self::FORM_REGISTRATION_FILE => 'form.registration_file',
        self::FORM_LICENCE_TYPE => 'form.licence_type',
        self::FORM_OVERVIEW => 'form.overview',
        self::FORM_MEMBER => 'form.member',
        self::FORM_KINSHIP => 'form.kinship',
    ];

    public const FORM_CHILDREN_RIGHT_IMAGE = 1;

    public const FORM_CHILDREN_GOING_HOME_ALONE = 2;

    public const FORMS_CHILDREN = [
        self::FORM_CHILDREN_RIGHT_IMAGE => 'form_children.right_image',
        self::FORM_CHILDREN_GOING_HOME_ALONE => 'form_children.going_home_alone',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (in_array($options['current']->getForm(), [self::FORM_HEALTH_QUESTION, self::FORM_HEALTH], true)) {
            $builder
                ->add('health', HealthType::class, [
                    'label' => false,
                    'current' => $options['current'],
                ])
            ;
        }

        if (self::FORM_MEMBER === $options['current']->getForm()) {
            $builder
                ->add('identities', CollectionType::class, [
                    'label' => false,
                    'entry_type' => IdentityType::class,
                    'entry_options' => [
                        'label' => false,
                        'category' => $options['category'],
                        'season_licence' => $options['season_licence'],
                        'is_kinship' => false,
                    ],
                ])
                ->add('licences', CollectionType::class, [
                    'label' => false,
                    'entry_type' => AdditionalFamilyMemberType::class,
                    'entry_options' => [
                        'label' => false,
                        'season_licence' => $options['season_licence'],
                        'is_kinship' => $options['is_kinship'],
                    ],
                ])
            ;
        }
        if (self::FORM_KINSHIP === $options['current']->getForm()) {
            $builder
                ->add('identities', CollectionType::class, [
                    'label' => false,
                    'entry_type' => IdentityType::class,
                    'entry_options' => [
                        'label' => false,
                        'category' => $options['category'],
                        'season_licence' => $options['season_licence'],
                        'is_kinship' => true,
                    ],
                ])
                ;
        }
        if (self::FORM_APPROVAL === $options['current']->getForm()) {
            $builder
                ->add('approvals', CollectionType::class, [
                    'label' => false,
                    'entry_type' => ApprovalType::class,
                    'entry_options' => [
                        'label' => false,
                        'current' => $options['current'],
                        'block_prefix' => 'customcheck',
                    ],
                ])
            ;
        }
        if (in_array($options['current']->getForm(), [self::FORM_LICENCE_COVERAGE, self::FORM_LICENCE_TYPE], true)) {
            $builder
                ->add('licences', CollectionType::class, [
                    'label' => false,
                    'entry_type' => LicenceType::class,
                    'entry_options' => [
                        'label' => false,
                        'season_licence' => $options['season_licence'],
                        'category' => $options['category'],
                        'current' => $options['current'],
                    ],
                ])
            ;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $user = $event->getData();
            $form = $event->getForm();

            if (empty($user->getLicenceNumber()) && self::FORM_MEMBER === $options['current']->getForm()) {
                $form->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Le mot de passe ne correspond pas.',
                    'options' => [
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                        'attr' => [
                            'data-constraint' => 'app-Password',
                            'data-multiple-fields' => 1,
                        ],
                    ],
                    'required' => true,
                    'first_options' => [
                        'label' => 'Mot de passe (6 caractères minimum)',
                    ],
                    'second_options' => [
                        'label' => 'Confirmation du mot de passe',
                    ],
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Le mot de passe ne peut pas être vide',
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Votre mot de passe doit faire au moins 6 caractères',
                            'max' => 10,
                            'maxMessage' => 'Votre mot de passe doit faire au plus 10 caractères',
                        ]),
                    ],
                ]);
            } else {
                $form->add('plainPassword', HiddenType::class, [
                    'mapped' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'current' => null,
            'is_kinship' => false,
            'category' => Licence::CATEGORY_ADULT,
            'season_licence' => null,
        ]);
    }
}
