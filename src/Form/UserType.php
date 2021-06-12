<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Licence;
use App\Form\HealthType;
use App\Form\AddressType;
use App\Form\IdentityType;
use App\Form\HealthQuestionType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UserType extends AbstractType
{
    public const FORM_HEALTH_QUESTION = 1;
    public const FORM_IDENTITY = 2;
    public const FORM_HEALTH = 3;
    public const FORM_APPROVAL = 4;
    public const FORM_LICENCE = 5;
    public const FORM_MEMBERSHIP_FEE = 6;
    public const FORM_REGISTRATION_FILE = 7;

    public const FORMS = [
        self::FORM_HEALTH_QUESTION => 'form.health_question',
        self::FORM_IDENTITY => 'form.identity',
        self::FORM_HEALTH => 'form.health',
        self::FORM_APPROVAL => 'form.approval_right_image',
        self::FORM_LICENCE => 'form.licence',
        self::FORM_MEMBERSHIP_FEE => 'form.membership_fee',
        self::FORM_REGISTRATION_FILE => 'form.registration_file',
    ];

    public const FORM_CHILDREN_RIGHT_IMAGE = 1;
    public const FORM_CHILDREN_GOING_HOME_ALONE = 2;

    public const FORMS_CHILDREN = [
        self::FORM_CHILDREN_RIGHT_IMAGE => 'form_children.right_image',
        self::FORM_CHILDREN_GOING_HOME_ALONE => 'form_children.going_home_alone',

    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (self::FORM_HEALTH_QUESTION === $options['current']->getForm()) {
            $builder
                ->add('health', HealthType::class, [
                    'label' => false,
                    'current' => $options['current']
                ]);
        }
        
        if (self::FORM_IDENTITY === $options['current']->getForm()) {
            $builder
                ->add('identities', CollectionType::class, [
                    'label' => false,
                    'entry_type' => IdentityType::class,
                    'entry_options' => [
                        'label' => false,
                        'is_kinship' => $options['is_kinship'],
                        'category' => $options['category']
                    ],
                ])
                ->add('licences', CollectionType::class, [
                    'label' => false,
                    'entry_type' => AdditionalFamilyMemberType::class,
                    'entry_options' => [
                        'label' => false,
                        'season_licence' => $options['season_licence'],
                        'is_kinship' => $options['is_kinship']
                    ],
                ]);
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
                    ],
                ]);
        }
        if (self::FORM_LICENCE === $options['current']->getForm()) {
            $builder
                ->add('licences', CollectionType::class, [
                    'label' => false,
                    'entry_type' => LicenceType::class,
                    'entry_options' => [
                        'label' => false,
                        'season_licence' => $options['season_licence'],
                        'category' => $options['category']
                    ],
                ]);
        }
        if (self::FORM_HEALTH === $options['current']->getForm()) {
            $builder->add('health', HealthType::class);
        }
        /*$builder
            ->add('save', SubmitType::class, [
                'label' => 'messages',
                'attr' => ['class' => 'btn btn-primary float-right'],
            ])
        ;*/

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $user = $event->getData();
            $form = $event->getForm();

            if (null === $user->getId() && self::FORM_IDENTITY === $options['current']->getForm()) {
                $form->add('plainPassword', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Le mot de passe ne correspond pas.',
                    'options' => [
                        'row_attr' => [
                            'class' => 'form-group-inline'
                        ],
                    ],
                    'required' => true,
                    'first_options'  => ['label' => 'Mot de passe'],
                    'second_options' => ['label' => 'Confirmation du mot de passe'],
                    'mapped' => false,
                    'constraints' => [
                        new NotBlank(['message' => 'Le mot de passe ne peut pas être vide']),
                        new Length(['min' => 6, 'minMessage' => 'Votre mot de passe doit faire au moins 6 caractères', 'max' => 10, 'maxMessage' => 'Votre mot de passe doit faire au plus 10 caractères']),
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
