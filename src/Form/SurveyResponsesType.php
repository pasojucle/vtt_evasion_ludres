<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SurveyResponsesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('surveyResponses', CollectionType::class, [
                'label' => false,
                'entry_type' => SurveyResponseType::class,
                'entry_options' => [
                    'label' => false,
                ],
            ])
            ;
            
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            if (null === $form->getParent()) {
                $form
                    ->add('submit', SubmitType::class, [
                        'label' => 'Enregistrer',
                        'attr' => [
                            'class' => 'btn btn-primary float-right',
                        ],
                    ]);
            }
        });
    }
}
