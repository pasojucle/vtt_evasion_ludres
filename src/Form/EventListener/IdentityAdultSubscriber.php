<?php

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Identity;
use App\Form\AddressType;
use App\Form\Admin\CommuneAutocompleteField;
use App\Service\LicenceService;
use App\Validator\BirthDate;
use App\Validator\Phone;
use App\Validator\SchoolTestingRegistration;
use App\Validator\UniqueMember;
use DateInterval;
use DateTime;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class IdentityAdultSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LicenceService $licenceService
    ) {
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    public function preSetData(FormEvent $event): void
    {
        /**@var Identity $identity */
        $identity = $event->getData();
        $form = $event->getForm();
        $isYearly = $form->getConfig()->getOption('is_yearly');
        $foreignBorn = !$identity->getBirthCommune()?->getPostalCode() && $identity->getId();
        $form
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'row_attr' => [
                    'class' => 'form-group',
                ],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                    new UniqueMember()
                ],
                'attr' => ($isYearly)
                    ? ['data-constraint' => '', 'data-form-validator-target' => 'field', ]
                    : ['data-constraint' => 'app-UniqueMember', 'data-form-validator-target' => 'field', ],
                'disabled' => $isYearly,
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'row_attr' => [
                    'class' => 'form-group',
                ],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                    new UniqueMember()
                ],
                'attr' => $isYearly
                    ? ['data-constraint' => '', 'autocomplete' => 'off', 'data-form-validator-target' => 'field', ]
                    : [
                        'data-constraint' => 'app-UniqueMember',
                        'data-multiple-fields' => 1,
                        'data-alert-route' => 'unique_member',
                        'autocomplete' => 'off',
                        'data-form-validator-target' => 'field',
                    ],
                'disabled' => $isYearly,
            ])
            ->add('address', AddressType::class, [
                'required' => true,
                'gardian' => 'member',
                'attr' => ['data-gardian' => 'member'],
            ])
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
                    'data-form-validator-target' => 'field',
                ],
            ])
            ->add('foreignBorn', CheckboxType::class, [
                'label' => 'Je suis né à l\'étranger',
                'mapped' => false,
                'required' => false,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'attr' => [
                    'data-action' => 'click->form-modifier#change',
                    'data-container-id' => 'birth-place',
                    'data-form-validator-target' => 'field',
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

        $options = $form->getConfig()->getOptions();
        $this->modifier($form, $options['category'], $foreignBorn);
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        if ($data) {
            $birthDate = (array_key_exists('birthDate', $data)) ? new dateTime($data['birthDate']) : null;
            $category = ($birthDate) ? $this->licenceService->getCategoryByBirthDate($birthDate) : LicenceCategoryEnum::ADULT;
            $foreignBorn = array_key_exists('foreignBorn', $data);
            $this->modifier($event->getForm(), $category, $foreignBorn);
        }
    }

    private function modifier(FormInterface $form, LicenceCategoryEnum $category, bool $foreignBorn): void
    {
        $isYearly = $form->getConfig()->getOption('is_yearly');
        $hidden = LicenceCategoryEnum::ADULT !== $category;
        $class = ($hidden) ? 'hidden' : 'form-group-inline';
        list($birthCommuneClass, $birthPlaceClass) = $this->getBirthPlaceClasses($foreignBorn);
        $dateMax = (new DateTime())->sub(new DateInterval('P5Y'));
        $dateMin = (new DateTime())->sub(new DateInterval('P80Y'));
        $form
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'attr' => [
                    'nin' => $dateMin->format('Y-m-d'),
                    'max' => $dateMax->format('Y-m-d'),
                    'data-max-date' => $dateMax->format('Y-m-d'),
                    'data-min-date' => $dateMin->format('Y-m-d'),
                    'data-year-range' => $dateMin->format('Y') . ':' . $dateMax->format('Y'),
                    'autocomplete' => 'off',
                    'data-constraint' => 'app-BirthDate;app-SchoolTestingRegistration',
                    'data-extra-param-name' => 'isYearly',
                    'data-extra-value' => (int) $isYearly,
                    'data-alert-route' => 'registration_scholl_testing_disabled',
                    'data-action' => 'change->form-modifier#change',
                    'data-container-id' => 'category-container',
                    'data-form-validator-target' => 'field',
                ],
                'row_attr' => [
                    'class' => 'form-group',
                ],
                'disabled' => $isYearly,
                'constraints' => [
                    new BirthDate(),
                ],
            ])
            ->add('birthCommune', CommuneAutocompleteField::class, [
                'label' => 'Lieu de naissance',
                'row_attr' => [
                    'class' => $birthCommuneClass,
                ],
                'attr' => [
                    'data-constraint' => '',
                    'data-form-validator-target' => 'field',
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
                    'data-form-validator-target' => 'field',
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
                    'data-form-validator-target' => 'field',
                ],
                'required' => $foreignBorn,
            ])
            ->add('email', EmailType::class, [
                'label' => LicenceCategoryEnum::SCHOOL === $category
                    ? '<p>Adresse mail (de l\'enfant)<br> Le mail de contact avec le club sera celui du parent</p>'
                    : 'Adresse mail',
                'label_html' => true,
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new Email(),
                ],
                'attr' => [
                    'data-constraint' => 'symfony-Email',
                    'autocomplete' => 'off',
                    'data-form-validator-target' => 'field',
                ],
            ])
            ->add('mobile', TextType::class, [
                'label' => LicenceCategoryEnum::SCHOOL === $category
                    ? 'Téléphone mobile (de l\'enfant)'
                    : 'Téléphone mobile',
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
                    'data-form-validator-target' => 'field',
                ],
            ])
            ->add('profession', TextType::class, [
                'label' => 'Profession',
                'row_attr' => [
                    'class' => $class,
                ],
                'attr' => [
                    'data-constraint' => '',
                    'data-form-validator-target' => 'field',
                ],
                'required' => false,
            ])
            ->add('emergencyPhone', TextType::class, [
                'label' => 'Télephone de la personne à prévenir en cas d\'urgence',
                'row_attr' => [
                    'class' => $class,
                ],
                'constraints' => [
                    new Phone(),
                ],
                'attr' => [
                    'data-constraint' => 'app-Phone',
                    'data-multiple-fields' => 1,
                    'autocomplete' => 'off',
                    'class' => 'phone-number',
                    'data-form-validator-target' => 'field',
                ],
                'required' => !$hidden,
            ])
            ->add('emergencyContact', TextType::class, [
                'label' => 'Lien de parenté (Mari, Femme, Fils, Fille, Frère, Sœur, Oncle, Tante, Grand parents.....)',
                'row_attr' => [
                    'class' => $class,
                ],
                'attr' => [
                    'data-constraint' => '',
                    'data-form-validator-target' => 'field',
                ],
                'required' => !$hidden,
            ])
        ;
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
