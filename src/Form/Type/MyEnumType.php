<?php

declare(strict_types=1);

namespace App\Form\Type;

use BackedEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyEnumType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_label' => static function (BackedEnum $choice): string {
                return $choice->value;
            },
        ]);
    }

    public function getParent()
    {
        return EnumType::class;
    }
}
