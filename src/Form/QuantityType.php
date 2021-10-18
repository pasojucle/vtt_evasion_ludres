<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class QuantityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'Quantité',
                'attr' => [
                    'min' => 1,
                ],
        ]);
    }

    public function getParent()
    {
        return IntegerType::class;
    }
}
