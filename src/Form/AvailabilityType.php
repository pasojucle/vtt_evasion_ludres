<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class AvailabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('availability', ChoiceType::class, [
                'label' => false,
                'choices' => array_flip(Session::AVAILABILITIES),
                'expanded' => true,
                'multiple' => false,
                'block_prefix' => 'customcheck',
            ])
        ;
    }
}
