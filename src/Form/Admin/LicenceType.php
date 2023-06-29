<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Licence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LicenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $licence = $event->getData();
            $form = $event->getForm();
            if ($licence === $options['season_licence']) {
                $statusChoices = [
                    'licence.status.waiting_validate' => Licence::STATUS_WAITING_VALIDATE,
                    'licence.status.valid' => ($options['season_licence']->isFinal()) ? Licence::STATUS_VALID : Licence::STATUS_TESTING,
                    'licence.status.in_processing' => Licence::STATUS_IN_PROCESSING,
                ];
                $form
                    ->add('final', ChoiceType::class, [
                        'label' => 'Période de 3 séances de test',
                        'choices' => [
                            'En cours' => false,
                            'Terminée' => true,
                        ],
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                    ->add('status', ChoiceType::class, [
                        'label' => 'Dossier d\'inscription',
                        'choices' => $statusChoices,
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                    ->add('isVae', CheckboxType::class, [
                        'label' => 'VTT à assistance électrique',
                        'required' => false,
                    ])
                    ->add('coverage', ChoiceType::class, [
                        'label' => 'Selectionnez une formule d\'assurance',
                        'choices' => array_flip(Licence::COVERAGES),
                        'row_attr' => [
                            'class' => 'form-group-inline',
                        ],
                    ])
                    ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Licence::class,
            'season_licence' => null,
        ]);
    }
}
