<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EventFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('period', ChoiceType::class, [
                'label' => false,
                'choices' => array_flip(Event::PERIODS),
                'attr' => [
                    'class' => 'btn',
                ],
            ])
            ->add('date', HiddenType::class)
            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $filters = $event->getData();
            $form = $event->getForm();
            if (!in_array($filters['period'], [Event::PERIOD_ALL, Event::PERIOD_NEXT], true)) {
                $form
                    ->add('prev', SubmitType::class, [
                        'label' => '<i class="fas fa-angle-left"></i>',
                        'label_html' => true,
                        'attr' => [
                            'class' => 'btn btn-default',
                            'title' => 'Précedent',
                        ],
                    ])
                    ->add('next', SubmitType::class, [
                        'label' => '<i class="fas fa-angle-right"></i>',
                        'label_html' => true,
                        'attr' => [
                            'class' => 'btn btn-default',
                            'title' => 'Suivant',
                        ],
                    ])
                    ->add('today', SubmitType::class, [
                        'label' => 'Aujourd\'hui',
                        'attr' => [
                            'class' => 'btn btn-default',
                            'title' => 'Aujourd\'hui',
                        ],
                    ])
                ;
            }
        });
    }
}
