<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\LicenceConsent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $licenceConsent = $event->getData();
            $form = $event->getForm();
            $form
                ->add('value', CheckboxType::class, [
                    'label' => $licenceConsent->getConsent()->getContent(),
                    'label_html' => true,
                ])
            ;
        });
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LicenceConsent::class,
        ]);
    }
}
