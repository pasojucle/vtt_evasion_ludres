<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\OrderHeader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderLines', CollectionType::class, [
                'label' => false,
                'entry_type' => OrderLineType::class,
            ])
            ->add('save', SubmitType::class, [
                'label' => '<i class="fas fa-check"></i> Valider la commande',
                'label_html' => true,
                'attr' => [
                    'class' => 'btn btn-primary float-right',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderHeader::class,
        ]);
    }
}
