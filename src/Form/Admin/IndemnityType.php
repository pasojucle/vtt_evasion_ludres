<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Indemnity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndemnityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var Indemnity $data */
            $data = $event->getData();
            $isCompensable = $form->getConfig()->getOption('is_compensable');

            $form
                ->add('amount', NumberType::class, [
                    'label' => $data->getLevel()->getTitle(),
                    'scale' => 2,
                    'html5' => true,
                    'attr' => [
                        'class' => 'align-right',
                        'step' => '0.01',
                        'placeholder' => '0.00',
                        'readonly' => !$isCompensable,
                    ],
                    'empty_data' => 0,
                ])
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Indemnity::class,
            'is_compensable' => false,
        ]);
    }
}
