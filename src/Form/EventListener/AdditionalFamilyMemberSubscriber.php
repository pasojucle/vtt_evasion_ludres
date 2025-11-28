<?php

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Entity\Licence;
use Symfony\Component\Form\FormEvent;
use App\Form\LicenceAutocompleteField;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdditionalFamilyMemberSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    )
    {
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
        $licence = $event->getData();
        $form = $event->getForm();
        $options = $form->getConfig()->getOptions();
        /** @var Licence $licence */
        $licence = $event->getData();
        $form = $event->getForm();
        if ($licence->getState()->isYearly() && !$options['is_kinship']) {
            $form
                ->add('additionalFamilyMember', CheckboxType::class, [
                    'label' => 'Un membre de ma famille est déjà inscrit au club',
                    'row_attr' => [
                        'class' => 'inputGroup check long form-group-inline',
                    ],
                    'attr' => [
                        'class' => 'form-modifier',
                        'data-modifier' => 'familyMember'
                    ],
                    'required' => false,
                ])
            ;
        }
        $this->modifier($event->getForm(), $licence->getAdditionalFamilyMember());
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $additionalFamilyMember = ($data && array_key_exists('additionalFamilyMember', $data)) 
            ? (bool)$data['additionalFamilyMember'] 
            : false;


        $this->modifier($event->getForm(), $additionalFamilyMember);
    }

    private function modifier(FormInterface $form, bool $additionalFamilyMember): void
    {
        if ($additionalFamilyMember) {
            $form
                ->add('familyMember', LicenceAutocompleteField::class, [
                    'label' => 'Numéro de licence du membre de la famille',
                    'autocomplete_url' => $this->urlGenerator->generate('licence_autocomplete'),
                    'attr' => [
                        'data-constraint' => 'app-LicenceNumber',
                    ],
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                ]);
        } else {
            $form
                ->add('familyMember', HiddenType::class, [
                    'attr' => [
                        'data-constraint' => 'app-LicenceNumber',
                    ],
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                    'data' => null,
                ]);
        }
    }
}
