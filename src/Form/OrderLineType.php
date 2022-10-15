<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\OrderLine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $orderLine = $event->getData();
            $form = $event->getForm();
            $form
                ->add('quantity', QuantityType::class, [
                    'label' => false,
                    'row_attr' => [
                        'class' => 'form-group',
                    ],
                    'attr' => [
                        'class' => 'orderline-quantity',
                        'min' => 1,
                    ],
                ])
                ->add('remove', SubmitType::class, [
                    'label' => 'supprimer',
                    'label_html' => true,
                    'attr' => [
                        'class' => 'orderline-remove',
                    ],
                ])
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderLine::class,
        ]);
    }
}
