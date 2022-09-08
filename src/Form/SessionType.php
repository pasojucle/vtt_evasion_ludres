<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitLabel = null;

        if (!$options['is_already_registered'] && !$options['is_end_testing']) {
            $submitLabel = (null !== $options['bike_ride'] && $options['bike_ride']->accessAvailability)
                ? 'Enregistrer' : 'S\'inscrire';
        }
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();
            if ($options['is_already_registered']) {
                $form->addError(new FormError('Votre inscription a déjà été prise en compte !'));
            } elseif ($options['is_end_testing']) {
                $form->addError(new FormError('Votre période d\'essai est terminée ! Pour continuer à participer aux sorties, inscrivez-vous.'));
            }

            if (null !== $data['responses']) {
                $form
                    ->add('responses', SurveyResponsesType::class, [
                        'label' => false,
                    ]);
            }
        });

        $builder
            ->add('session', SessionEditType::class, [
                'label' => false,
                'clusters' => [],
                'bike_ride' => $options['bike_ride'],
                'is_already_registered' => false,
                'is_end_testing' => false,
                'submited_label' => $submitLabel,
            ])
        ;

        if (null !== $submitLabel) {
            $builder
                ->add('submit', SubmitType::class, [
                    'label' => '<i class="fas fa-chevron-circle-right"></i> ' . $submitLabel,
                    'label_html' => true,
                    'attr' => [
                        'class' => 'btn btn-primary float-right',
                    ],
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'clusters' => [],
            'bike_ride' => null,
            'is_already_registered' => false,
            'is_end_testing' => false,
        ]);
    }
}
