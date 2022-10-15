<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class QuantityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'label' => 'Quantité',
            'attr' => [
                'min' => 1,
            ],
            'constraints' => [
                new Range(['min' =>1]),
            ]
        ]);
    }

    public function getParent()
    {
        return IntegerType::class;
    }
}
