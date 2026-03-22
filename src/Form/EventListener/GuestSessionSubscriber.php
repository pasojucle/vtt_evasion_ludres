<?php

declare(strict_types=1);

namespace App\Form\EventListener;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class GuestSessionSubscriber implements EventSubscriberInterface
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
        $session = $event->getData();
        $form = $event->getForm();
        
        ;

        
        $this->modifier($form, $session->getLastLicence()->isFFVelo());
    }

    public function preSubmit(FormEvent $event): void
    {
        $session = $event->getData();
        $form = $event->getForm();

        $isFFVelo = array_key_exists('lastLicence', $session) && array_key_exists('FFVelo', $session['lastLicence'])
            ? (bool) $session['lastLicence']['FFVelo']
            : false;

        $this->modifier($form, $isFFVelo);
    }

    private function modifier(FormInterface $form, ?bool $isFFVelo): void
    {
        if ($isFFVelo) {
            $form
                ->add('licenceNumber', TextType::class, [
                    'label' => 'numéro de licence',
                    'row_attr' => [
                        'class' => 'form-group-inline',
                    ],
                ]);
        } else {
            $form
                ->add('licenceNumber', HiddenType::class, [
                    'data' => null,
                ]);
        }

    }
}
