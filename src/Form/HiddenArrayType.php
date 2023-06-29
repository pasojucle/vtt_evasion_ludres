<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Transformer\HiddenArrayTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Entity hidden custom type class definition.
 */
class HiddenArrayType extends HiddenType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // attach the specified model transformer for this entity list field
        // this will convert data between object and string formats

        $builder->addModelTransformer(new HiddenArrayTransformer());
    }
}
