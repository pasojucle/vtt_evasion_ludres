<?php

declare(strict_types=1);

namespace App\Form;


use App\Form\Transformer\HiddenArrayTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Entity hidden custom type class definition.
 */
class HiddenArrayType extends HiddenType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // attach the specified model transformer for this entity list field
        // this will convert data between object and string formats

        $builder->addModelTransformer(new HiddenArrayTransformer());
    }
}
