<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\LicenceSwornCertification;
use App\Entity\SwornCertification;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SwornCertificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $swornCertification = $event->getData();
            $form = $event->getForm();

            $form
                ->add('value', CheckboxType::class, [
                    'label' => $swornCertification->getSwornCertification()->getLabel(),
                    'label_html' => true,
                ])
            ;
        });
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LicenceSwornCertification::class,
        ]);
    }
}
