<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Level;
use App\Entity\Licence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class UserFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nom ou prénom'
                ],
                'required' => false,
            ])
            ->add('category', ChoiceType::class, [
                'label' => false,
                'choices' => array_flip(Licence::CATEGORIES),
                'attr' => [
                    'class' => 'btn',
                ]
            ])
            ->add('level', EntityType::class, [
                'label' => false,
                'class' => Level::class, 
                'attr' => [
                    'class' => 'btn',
                ]
            ])
            ;
    }
}