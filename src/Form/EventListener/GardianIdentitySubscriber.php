<?php

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Entity\Enum\GardianKindEnum;
use App\Form\AddressType;
use App\Validator\BirthDate;
use App\Validator\Phone;
use App\Validator\UniqueMember;
use DateInterval;
use DateTime;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class GardianIdentitySubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preSubmit',
        ];
    }

    public function preSetData(FormEvent $event): void
    {
        $identity = $event->getData();
        $form = $event->getForm();
        $options = $form->getConfig()->getOptions();
        $dateMax = (new DateTime())->sub(new DateInterval('P5Y'));
        $dateMin = (new DateTime())->sub(new DateInterval('P100Y'));
        $otherAddress = $identity->hasAddress();
        $form
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                    new UniqueMember()
                ],
                'attr' => ['data-constraint' => ''],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'row_attr' => [
                    'class' => 'form-group-inline',
                ],
                'constraints' => [
                    new NotNull(),
                    new NotBlank(),
                    new UniqueMember()
                ],
                'attr' => ['data-constraint' => '', 'autocomplete' => 'off', ],
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
                'label' => 'Adresse mail',
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
            ->add('otherAddress', CheckboxType::class, [
                'label' => 'Réside à une autre adresse que l\'enfant',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-modifier',
                    'data-modifier' => sprintf('address-container-%s', $options['gardian']->value)
                ],
                'data' => $otherAddress,
            ])
        ;

        if (GardianKindEnum::LEGAL_GARDIAN == $options['gardian']) {
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
                    'attr' => [
                        'nin' => $dateMin->format('Y-m-d'),
                        'max' => $dateMax->format('Y-m-d'),
                        'data-max-date' => $dateMax->format('Y-m-d'),
                        'data-min-date' => $dateMin->format('Y-m-d'),
                        'data-year-range' => $dateMin->format('Y') . ':' . $dateMax->format('Y'),
                        'autocomplete' => 'off',
                        'data-constraint' => 'app-BirthDate',
                    ],
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'constraints' => [
                        new BirthDate(),
                    ],
                ])
                ;
            }
        $this->modifier($form, $otherAddress);
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $otherAddress = ($data && array_key_exists('otherAddress', $data)) ? (bool) $data['otherAddress'] : false;
        if (!$otherAddress) {
            $data['address'] = null;
            $event->setData($data);
        }

        $this->modifier($event->getForm(), $otherAddress);
    }

    private function modifier(FormInterface $form, bool $otherAddress): void
    {
        $form
            ->add('address', AddressType::class, [
                'row_class' => ($otherAddress) ? 'identity-address' : 'identity-address hidden',
                'required' => $otherAddress,
                'gardian' => $form->getConfig()->getOption('gardian')->value
            ]);
    }
}
