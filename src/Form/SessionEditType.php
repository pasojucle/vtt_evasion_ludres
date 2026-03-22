<?php

declare(strict_types=1);

namespace App\Form;


use App\Entity\Session;
use App\Entity\User;
use App\Form\EventListener\SessionSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', HiddenEntityType::class, [
                'class' => User::class,
            ])
            ->addEventSubscriber(new SessionSubscriber());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            'clusters' => [],
            'is_writable_availability' => false,
            'display_bike_kind' => false,
        ]);
    }
}
