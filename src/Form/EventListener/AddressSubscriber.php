<?php

declare(strict_types=1);

namespace App\Form\EventListener;

use App\Entity\Commune;
use App\Repository\CommuneRepository;
use App\Validator\PostalCode;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Length;

class AddressSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CommuneRepository $communeRepository,
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
        $data = $event->getData();

        $this->modifier($event->getForm(), $data?->getPostalCode());
    }

    public function preSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        $postalCode = ($data && array_key_exists('postalCode', $data)) ? $data['postalCode'] : null;

        $this->modifier($event->getForm(), $postalCode);
    }

    private function modifier(FormInterface $form, ?string $postalCode): void
    {
        $options = $form->getConfig()->getOptions();
        $form
            ->add('postalCode', TextType::class, [
                'label' => 'Code postal',
                'constraints' => [
                    new Length([
                        'min' => 5,
                        'max' => 5,
                    ]),
                    new PostalCode()
                ],
                'row_attr' => [
                    'class' => 'form-group-inline' . $options['row_class'],
                ],
                'attr' => [
                    'data-constraint' => 'app-PostalCode',
                    'class' => 'form-modifier',
                    'data-modifier' => sprintf('commune-%s', $form->getParent()->getName())
                ],
                'required' => $options['required'],
            ])
            ->add('commune', EntityType::class, [
                'label' => 'Ville',
                'class' => Commune::class,
                'choice_label' => 'name',
                'choices' => $this->communes($postalCode),
                'placeholder' => 'Sélectionner une commune',
                'row_attr' => [
                    'class' => 'form-group-inline' . $options['row_class'],
                    'id' => 'commune-' . $form->getParent()->getName(),
                ],
                'attr' => [
                    'data-constraint' => 'app-PostalCode',
                    'data-multiple-fields' => 1,
                ],
                'required' => $options['required'],
            ])
            ;
    }

    private function communes(?string $postalCode): array
    {
        if (empty($postalCode)) {
            return [];
        }

        return $this->communeRepository->findByPostalCode($postalCode);
    }
}
