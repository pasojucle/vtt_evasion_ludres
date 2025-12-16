<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\RegistrationFormEnum;
use App\Entity\User;
use App\Form\HealthType;
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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (in_array($options['current']->getForm(), [RegistrationFormEnum::HEALTH_QUESTION, RegistrationFormEnum::HEALTH], true)) {
            $builder
                ->add('health', HealthType::class, [
                    'label' => false,
                    'current' => $options['current'],
                ])
            ;
        }

        if (RegistrationFormEnum::MEMBER === $options['current']->getForm()) {
            $builder
                ->add('identity', IdentityType::class, [
                    'label' => false,
                    'category' => $options['category'],
                    'is_gardian' => false,
                    'is_yearly' => $options['season_licence']?->isYearly,
                ])
                ->add('lastLicence', AdditionalFamilyMemberType::class, [
                    'label' => false,
                    'season_licence' => $options['season_licence'],
                    'is_gardian' => $options['is_gardian']
                ])
            ;
        }
        if (RegistrationFormEnum::GARDIANS === $options['current']->getForm()) {
            $builder
                ->add('userGardians', CollectionType::class, [
                    'label' => false,
                    'entry_type' => GardianType::class,
                    'entry_options' => [
                        'label' => false,
                        'category' => $options['category'],
                        'is_yearly' => $options['season_licence']->isYearly,
                    ],
                ])
                ;
        }
        
        if (in_array($options['current']->getForm(), [RegistrationFormEnum::LICENCE_COVERAGE, RegistrationFormEnum::OVERVIEW, RegistrationFormEnum::LICENCE_AGREEMENTS, RegistrationFormEnum::HEALTH_QUESTION], true)) {
            $builder
                ->add('lastLicence', LicenceType::class, [
                    'label' => false,
                    'season_licence' => $options['season_licence'],
                    'category' => $options['category'],
                    'current' => $options['current']
                ])
            ;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $user = $event->getData();
            $form = $event->getForm();

            if (empty($user->getLicenceNumber()) && RegistrationFormEnum::MEMBER === $options['current']->getForm()) {
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
                            'autocomplete' => 'new-password',
                        ],
                    ],
                    'required' => true,
                    'first_options' => [
                        'label' => 'Mot de passe (6 caractères mini, 10 max)',
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'current' => null,
            'is_gardian' => false,
            'category' => LicenceCategoryEnum::ADULT,
            'season_licence' => null,
        ]);
    }
}
