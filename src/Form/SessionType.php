<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitLabel = (true === $options['is_writable_availability']) ? 'Enregistrer' : 'S\'inscrire';

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

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
                'clusters' => $options['clusters'],
                'is_writable_availability' => $options['is_writable_availability'],
            ])

            ->add('submit', SubmitType::class, [
                'label' => '<i class="fas fa-chevron-circle-right"></i> ' . $submitLabel,
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'clusters' => [],
            'is_writable_availability' => false,
        ]);
    }
}
