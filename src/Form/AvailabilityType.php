<?php

namespace App\Form;


use App\Entity\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

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
