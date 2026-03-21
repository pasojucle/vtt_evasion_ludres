<?php

namespace App\Form;

use App\Entity\Guest;
use App\Form\EventListener\GuestSessionSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GuestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('identity', GuestIdentityType::class, [
                'label' => false,
            ])
            ->add('lastLicence', GuestLicenceType::class, [
                'label' => false,
            ])
            ->addEventSubscriber(new GuestSessionSubscriber())
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Guest::class,
            'attr' => [
                'data-controller' => 'form-modifier'
            ]
        ]);
    }
}
