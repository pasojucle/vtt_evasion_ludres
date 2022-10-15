<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\OrderHeader;
use App\Form\OrderLineType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderLines', CollectionType::class, [
                'label' => false,
                'entry_type' => OrderLineType::class,
            ])
        ;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            if (!$data->getOrderLines()->isEmpty()) {
                $form
                    ->add('save', SubmitType::class, [
                        'label' => '<i class="fas fa-check"></i> Valider la commande',
                        'label_html' => true,
                        'attr' => [
                            'class' => 'btn btn-primary float-right',
                        ],
                    ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderHeader::class,
        ]);
    }
}
