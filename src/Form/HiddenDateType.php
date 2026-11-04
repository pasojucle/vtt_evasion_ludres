<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Transformer\HiddenDateTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Enum hidden custom type class definition.
 */
class HiddenDateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new HiddenDateTransformer());
    }

    public function getParent(): string
    {
        // On déclare proprement que notre champ se base sur un HiddenType
        return HiddenType::class;
    }
}
